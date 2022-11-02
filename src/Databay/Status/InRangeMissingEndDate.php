<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;
use ilDatetime;

class InRangeMissingEndDate implements Status
{
    /** @var ilDatetime */
    private $start;

    public function __construct(ilDateTime $start)
    {
        $this->start = $start;
    }

    public function name(): string
    {
        return 'in_range_missing_end';
    }

    public function string(callable $txt): string
    {
        return sprintf($txt('in_range_missing_end_format'));
    }

    public function userCanEnter(): bool
    {
        return true;
    }
}
