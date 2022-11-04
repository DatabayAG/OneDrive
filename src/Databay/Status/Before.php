<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;
use ilDateTime;
use DateTimeImmutable;

class Before implements Status
{
    /** @var ilDatetime */
    private $now;
    /** @var ilDatetime */
    private $start;

    public function __construct(ilDateTime $now, ilDateTime $start)
    {
        $this->now = $now;
        $this->start = $start;
    }

    public function name(): string
    {
        return 'before_range';
    }

    public function string(callable $txt): string
    {
        $date = $this->start->get(IL_CAL_FKT_GETDATE);
        return sprintf($txt('before_range_format'), $date['mday'], $date['mon'], $date['year'], $date['hours'], $date['minutes']);
    }

    public function userCanEnter(): bool
    {
        return false;
    }
}
