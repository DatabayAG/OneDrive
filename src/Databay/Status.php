<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay;

interface Status
{
    public function name(): string;
    public function string(callable $txt): string;
    public function userCanEnter(): bool;
}
