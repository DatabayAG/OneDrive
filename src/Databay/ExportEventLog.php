<?php

declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay;

use srag\Plugins\OneDrive\EventLog\EventLogEntryAR;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Stream\Streams;
use Closure;
use ilCloudPluginUploadGUI;
use srag\Plugins\OneDrive\EventLog;
use srag\Plugins\OneDrive\Databay;

class ExportEventLog
{
    /** @var Container */
    private $container;
    /** @var Databay */
    private $databay;
    /** @var int */
    private $id;

    public function __construct(Container $container, Databay $databay, int $id)
    {
        $this->container = $container;
        $this->databay = $databay;
        $this->id = $id;
    }

    public function render(): string
    {
        $button = $this->container->ui()->factory()->button()->standard(
            $this->databay->txt('export_event_log'),
            $this->container->ctrl()->getLinkTargetByClass(ilCloudPluginUploadGUI::class, 'exportEventLog')
        );
        return $this->container->ui()->renderer()->render($button) . '<style>#root{margin-top: 20px;}</style>';
    }

    public function export(): void
    {
        $logs = EventLogEntryAR::where(['obj_id' => $this->id])->get();
        $header = array_map([$this->databay, 'txt'], ['id', 'cloud_object_id', 'timestamp', 'username', 'event_type', 'path', 'object_type', 'additional_data']);
        $header[3] = $this->databay->iliasTxt('username');

        $this->csvExportFrom(function (EventLogEntryAR $entry, $key): array {
            return [
                $entry->getId(),
                $entry->getObjId(),
                $entry->getTimestamp(),
                $this->databay->userNameOf($entry->getUserId()),
                $this->databay->txt($entry->getEventType()->value()),
                $entry->getPath(),
                $this->databay->txt($entry->getObjectType()->value()),
                json_encode($this->translateStatus($entry->getAdditionalData()))
            ];
        }, $logs, $header);
    }

    private function csvExportFrom(Closure $transform, array $array, array $header = null): void
    {
        $fd = fopen('php://temp', 'rw');
        if (null !== $header) {
            fputcsv($fd, $header);
        }
        foreach ($array as $key => $value) {
            fputcsv($fd, $transform($value, $key));
        }

        $this->respondCsv($fd); // Resource is closed by the Stream class.
    }

    private function respondCsv($resource): void
    {
        $this->databay->sendResponse(Streams::ofResource($resource), 'text/csv');
    }

    private function translateStatus(array $data): array
    {
        if (isset($data['status'])) {
            $data['status'] = $this->databay->txt($data['status']);
        }

        return $data;
    }
}
