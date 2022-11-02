<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;
use ilDatetime;

class InRange implements Status
{
    /** @var ilDatetime */
    private $now;
    /** @var ilDatetime */
    private $start;
    /** @var ilDatetime */
    private $end;

    public function __construct(ilDateTime $now, ilDateTime $start, ilDateTime $end)
    {
        $this->now = $now;
        $this->start = $start;
        $this->end = $end;
    }

    public function name(): string
    {
        return 'in_range';
    }

    public function string(callable $txt): string
    {
        $diff = (new DateTimeImmutable('@' . $this->now->getUnixTime()))->diff(
            new DateTimeImmutable('@' . $this->end->getUnixTime())
        );

        return sprintf(
            $txt('in_range_format'),
            $diff->days,
            $diff->h,
            $diff->i
        );
    }

    public function userCanEnter(): bool
    {
        return true;
    }
}
