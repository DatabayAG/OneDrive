<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;
use ilDateTime;

class Before implements Status
{
    /** @var ilDatetime */
    private $start;

    public function __construct(ilDateTime $start)
    {
        $this->start = $start;
    }

    public function name(): string
    {
        return 'before_range';
    }

    public function string(callable $txt): string
    {
        $diff = (new DateTimeImmutable('@' . $this->now->getUnixTime()))->diff(
            new DateTimeImmutable('@' . $this->start->getUnixTime())
        );
        return sprintf($txt('before_range_format'), $diff->days, $diff->h, $diff->i);
    }

    public function userCanEnter(): bool
    {
        return false;
    }
}
