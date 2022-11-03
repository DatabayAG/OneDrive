<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive;

use ILIAS\DI\Container;
use srag\Plugins\OneDrive\Databay\Settings;
use ilObjCloud;
use ilCloudFileNode;
use srag\Plugins\OneDrive\EventLog\EventLogEntryAR;
use srag\Plugins\OneDrive\EventLog\EventType;
use srag\Plugins\OneDrive\Databay\ExportEventLog;
use ilOneDrivePlugin;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\Stream;
use ilUtil;
use Psr\Http\Message\ResponseInterface as Response;
use Exception;
use ilLink;
use ilObjCloudGUI;

class Databay
{
    /** @var Container */
    private $container;

    /** @var ilOneDrivePlugin */
    private $plugin;

    public function __construct(Container $container, ilOneDrivePlugin $plugin)
    {
        $this->container = $container;
        $this->plugin = $plugin;
    }

    public function beforeSetContent(ilObjCloud $obj): array
    {
        $settings = Settings::fromCloudId((int) $obj->getId());

        return [
            'text' => $this->determineStatusText($settings),
            'permaLink' => $this->permaLink(),
        ];
    }

    public function textOfUploadedFile(ilCloudFileNode $node, int $obj_id): string
    {
        $db = $this->container->database();
        $where = sprintf(
            'obj_id = %s AND path = %s AND event_type = %s',
            $db->quote($obj_id, 'integer'),
            $db->quote($node->getPath(), 'text'),
            $db->quote(EventType::UPLOAD_STARTED, 'text')
        );
        $result = $db->query(sprintf(
            'SELECT timestamp, additional_data from %s WHERE %s AND timestamp = (SELECT MAX(timestamp) FROM %s WHERE %s)',
            EventLogEntryAR::DB_TABLE_NAME,
            $where,
            EventLogEntryAR::DB_TABLE_NAME,
            $where
        ));
        $result = $db->fetchAssoc($result);
        if (!$result) {
            return '';
        }
        $additional_data = json_decode($result['additional_data'] ?? '', true);

        return ' ' . $this->plugin->txt($additional_data['status'] ?? '');
    }

    public function extraData(int $obj_id): array
    {
        $settings = Settings::fromCloudId($obj_id);

        return [
            'status' => $settings->status()->name(),
        ];
    }

    public function usersCanUpload(int $obj_id): bool
    {
        return $this->userCanEnter($this->refId(), Settings::fromCloudId($obj_id));
    }

    public function exportEventLog(int $obj_id): ExportEventLog
    {
        return new ExportEventLog($this->container, $this, $obj_id);
    }

    private function userCanEnter(int $ref_id, Settings $settings): bool
    {
        return $this->isAdmin($ref_id) || $settings->status()->userCanEnter();
    }

    private function refId(): int
    {
        $params = $this->container->http()->request()->getQueryParams();
        if (isset($params['ref_id'])) {
            return (int) $params['ref_id'];
        } elseif ($this->container->http()->request()->getUri()->getPath() === '/goto.php' && isset($params['target']) && preg_match('/^cld_(\d+)/', $params['target'], $matches)) {
            $this->container->ctrl()->setParameterByClass(ilObjCloudGUI::class, 'ref_id', $matches[1]);
            $this->container->ctrl()->redirectToURL($this->container->ctrl()->getLinkTargetByClass(ilObjCloudGUI::class, ''));
        }

        throw new Exception('Missing query parameter ref_id.');
    }

    private function isAdmin(int $id): bool
    {
        return $this->container->access()->checkAccess('write', '', $id);
    }

    private function determineStatusText(Settings $settings): string
    {
        return $settings->status()->string([$this, 'txt']);
    }

    public function uploadSuccess(EventLogEntryAR $entry): void
    {
        $this->send($this->uploadResult($entry));
    }

    private function uploadResult(EventLogEntryAR $entry): Response
    {
        $start_entry = $this->startEntryOf($entry) ?? $entry; // If the requests are too fast.
        $render = [$this->container->ui()->renderer(), 'render'];
        $result = $this->txt($entry->getEventType()->value());

        $properties = [
            $this->txt('event_type') => $result,
            $this->txt('upload_start') => $start_entry->getTimestamp(),
            $this->txt('upload_end') => $entry->getTimestamp(),
            $this->iliasTxt('username') => $this->userNameOf($entry->getUserId()),
            $this->txt('path') => $this->removeRoot($entry->getPath()),
        ];

        if (isset($entry->getAdditionalData()['status'])) {
            $properties[$this->txt('range')] = $this->txt($entry->getAdditionalData()['status']);
        }

        $item = $this->container->ui()->factory()->item()->standard($this->txt('upload_summary'))->withProperties($properties);
        $modal = $this->container->ui()->factory()->modal();
        $lightbox = $modal->lightbox([$modal->lightboxTextPage($render($item), $result)]);

        return $this->createResponseHTML($render($lightbox));
    }

    private function removeRoot(string $path): string
    {
        return implode('/', array_slice(explode('/', $path), 2));
    }

    private function startEntryOf(EventLogEntryAR $entry)
    {
        $entries = EventLogEntryAR::where([
            'obj_id' => $entry->getObjId(),
            'path' => $entry->getPath(),
            'event_type' => EventType::UPLOAD_STARTED,
        ])->get();

        // Pick latest entry.
        usort($entries, function (EventLogEntryAR $a, EventLogEntryAR $b): int {
            return strcmp($a->getTimestamp(), $b->getTimestamp());
        });

        return end($entries) ?: null;
    }

    public function uploadFailed(EventLogEntryAR $entry, int $status = 200): void
    {
        $this->send($this->uploadResult($entry)->withStatus($status));
    }

    public function userNameOf(int $user_id): string
    {
        $user = $this->container->user();
        if ((int) $user->getId() === $user_id) {
            return $this->formatUser($user->getFirstname(), $user->getLastname(), $user->getLogin());
        }

        $database = $this->container->database();
        $result = $database->queryF('SELECT firstname, lastname, login FROM usr_data WHERE usr_id = %s', ['integer'], [$user_id]);
        $result = $database->fetchAssoc($result);
        if (!$result) {
            throw new Exception('Could not find user with id: ' . $user_id);
        }

        return $this->formatUser($result['firstname'], $result['lastname'], $result['login']);
    }

    public function createResponseHTML(string $content): Response
    {
        return $this->createResponse(Streams::ofString($content), 'text/html');
    }

    public function createResponse(Stream $stream, string $content_type): Response
    {
        $response = $this->container->http()->response()->withBody($stream);
        $response = $response->withHeader('Content-Type', $content_type);

        return $response;
    }

    public function sendResponse(Stream $stream, string $content_type): void
    {
        $response = $this->createResponse($stream, $content_type);
        $this->send($response);
    }

    public function send(Response $response): void
    {
        $this->container->http()->saveResponse($response);
        $this->container->http()->sendResponse();
        $this->close();
    }

    private function formatUser(string $firstname, string $lastname, string $login): string
    {
        return sprintf('%s %s %s', $firstname, $lastname, $login);
    }

    public function txt(string $string): string
    {
        return $this->plugin->txt($string);
    }

    public function iliasTxt(string $string): string
    {
        return $this->container->language()->txt($string);
    }

    private function close(): void
    {
        if ($this->newerThanILIAS5()) { // In ILIAS 5 ILIAS\HTTP\GlobalHttpState::close(...) doesn't exist.
            $this->container->http()->close();
        } else {
            exit;
        }
    }

    private function newerThanILIAS5(): bool
    {
        return current(explode('.', ILIAS_VERSION_NUMERIC)) > 5;
    }

    private function permaLink(): string
    {
        return ilLink::_getStaticLink($this->refId(), 'cld');
    }
}
