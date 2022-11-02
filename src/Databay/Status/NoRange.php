<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay\Status;

use srag\Plugins\OneDrive\Databay\Status;

class NoRange implements Status
{
    public function name(): string
    {
        return 'no_range';
    }

    public function string(callable $txt): string
    {
        return $txt('no_range_format');
    }

    public function userCanEnter(): bool
    {
        return false;
    }
}
