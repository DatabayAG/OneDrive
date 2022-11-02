<?php declare(strict_types=1);

namespace srag\Plugins\OneDrive\Databay;

use ilSetting;
use ilOneDriveSettingsGUI;
use ilDateTime;
use ilCalendarUtil;
use srag\Plugins\OneDrive\Databay\Status\NoRange;
use srag\Plugins\OneDrive\Databay\Status\InRange;
use srag\Plugins\OneDrive\Databay\Status\After;
use srag\Plugins\OneDrive\Databay\Status\Before;
use srag\Plugins\OneDrive\Databay\Status\Extra;
use srag\Plugins\OneDrive\Databay\Status\InRangeMissingEndDate;

class Settings
{
    /** @var ilSetting */
    private $settings;

    public function __construct(ilSetting $settings)
    {
        $this->settings = $settings;
    }

    public static function fromCloudId(int $id): self
    {
        return new self(ilOneDriveSettingsGUI::createSettings($id));
    }

    public function status(): Status
    {
        if (!$this->startDateExists()) {
            return new NoRange();
        }

        if ($this->isBeforeStartDate()) {
            return new Before($this->startDate());
        }

        if (!$this->lastDateExists()) {
            return new InRangeMissingEndDate($this->startDate());
        }

        if ($this->isAfterLastDate()) {
            return new After();
        }

        if ($this->extraDateExists() && $this->isAfterEndDate()) {
            return new Extra($this->now(), $this->startDate(), $this->endDate(), $this->extraDate());
        }

        return new InRange($this->now(), $this->startDate(), $this->endDate());
    }

    private function startDateExists(): bool
    {
        return (bool) $this->startDate();
    }

    private function isBeforeStartDate(): bool
    {
        $now = $this->now();

        // var_dump($now.'', $this->startDate().'');exit;
        return ilDateTime::_before($now, $this->startDate());
    }

    private function startDate(): ?ilDateTime
    {
        return ilCalendarUtil::parseIncomingDate($this->settings->get('start_date', ''));
    }

    private function lastDateExists(): bool
    {
        return (bool) $this->endDate();
    }

    private function endDate(): ?ilDateTime
    {
        return ilCalendarUtil::parseIncomingDate($this->settings->get('end_date', ''));
    }

    private function extraDate(): ?ilDateTime
    {
        return ilCalendarUtil::parseIncomingDate($this->settings->get('extra_date', ''));
    }

    private function isAfterLastDate(): bool
    {
        if (!$this->isAfterEndDate()) {
            return false;
        }
        if (!$this->extraDateExists()) {
            return true;
        }

        return ilDateTime::_after($this->now(), $this->extraDate());
    }

    private function now(): ilDateTime
    {
        return new ilDateTime(time(), IL_CAL_UNIX);
    }

    private function isAfterEndDate(): bool
    {
        $end = $this->endDate();
        if (!$end) {
            return false;
        }

        return ilDateTime::_after($this->now(), $end);
    }

    private function extraDateExists(): bool
    {
        return (bool) $this->extraDate();
    }
}
