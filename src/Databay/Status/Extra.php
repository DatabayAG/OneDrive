<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;
use ilDatetime;
use DateTimeImmutable;

class Extra implements Status
{

    /** @var ilDatetime */
    private $now;
    /** @var ilDatetime */
    private $start;
    /** @var ilDatetime */
    private $end;
    /** @var ilDatetime */
    private $extra;

    public function __construct(ilDateTime $now, ilDateTime $start, ilDateTime $end, ilDateTime $extra)
    {
        $this->now = $now;
        $this->start = $start;
        $this->end = $end;
        $this->extra = $extra;
    }

    public function name(): string
    {
        return 'extra_range';
    }

    public function string(callable $txt): string
    {
        $diff = (new DateTimeImmutable('@' . $this->now->getUnixTime()))->diff(
            new DateTimeImmutable('@' . $this->extra->getUnixTime())
        );

        return sprintf($txt('extra_range_format'), $diff->days, $diff->h, $diff->i);
    }

    public function userCanEnter(): bool
    {
        return true;
    }
}
