<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours\data;

use craft\helpers\DateTimeHelper;
use DateTime;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
    /**
     * Returns Sunday’s hours
     *
     * @return DayData
     */
    public function getSun(): DayData
    {
        return $this[0];
    }

    /**
     * Returns Monday’s hours
     *
     * @return DayData
     */
    public function getMon(): DayData
    {
        return $this[1];
    }

    /**
     * Returns Tuesday’s hours
     *
     * @return DayData
     */
    public function getTue(): DayData
    {
        return $this[2];
    }

    /**
     * Returns Wednesday’s hours
     *
     * @return DayData
     */
    public function getWed(): DayData
    {
        return $this[3];
    }

    /**
     * Returns Thursday’s hours
     *
     * @return DayData
     */
    public function getThu(): DayData
    {
        return $this[4];
    }

    /**
     * Returns Friday’s hours
     *
     * @return DayData
     */
    public function getFri(): DayData
    {
        return $this[5];
    }

    /**
     * Returns Saturday’s hours
     *
     * @return DayData
     */
    public function getSat(): DayData
    {
        return $this[6];
    }

    /**
     * Returns today’s hours.
     *
     * @return DayData
     */
    public function getToday(): DayData
    {
        return $this->_hoursByDate(new DateTime());
    }

    /**
     * Returns yesterday’s hours.
     *
     * @return DayData
     */
    public function getYesterday(): DayData
    {
        return $this->_hoursByDate(new DateTime('-1 day'));
    }

    /**
     * Returns tomorrow’s hours.
     *
     * @return DayData
     */
    public function getTomorrow(): DayData
    {
        return $this->_hoursByDate(new DateTime('+1 day'));
    }

    /**
     * Returns whether we are currently within open hours.
     *
     * @return bool
     * @since 4.1.0
     */
    public function getIsOpen(): bool
    {
        $today = $this->getToday();
        try {
            /** @var DateTime|null $open */
            /** @phpstan-ignore-next-line */
            $open = $today->open;
            /** @var DateTime|null $close */
            /** @phpstan-ignore-next-line */
            $close = $today->close;
            /** @phpstan-ignore-next-line */
        } catch (UnknownPropertyException $e) {
            throw new InvalidConfigException('Calling getIsOpen() on Store Hours field data is only allowed for fields that define `open` and `close` time slots.', previous: $e);
        }
        if (!$open || !$close) {
            return false;
        }
        $now = $this->minuteOfDay(DateTimeHelper::now());
        return $now >= $this->minuteOfDay($open) && $now < $this->minuteOfDay($close);
    }

    private function minuteOfDay(DateTime $date): int
    {
        return (int)$date->format('G') * 60 + (int)ltrim($date->format('s'), '0');
    }

    /**
     * @param DateTime $date
     * @return DayData
     */
    private function _hoursByDate(DateTime $date): DayData
    {
        $day = (int)$date->format('w');
        return $this[$day];
    }

    /**
     * Returns a range of the days.
     *
     * Specify days using these integers:
     *
     * - `0` – Sunday
     * - `1` – Monday
     * - `2` – Tuesday
     * - `3` – Wednesday
     * - `4` – Thursday
     * - `5` – Friday
     * - `6` – Saturday
     *
     * For example, `getRange(1, 5)` would give you data for Monday-Friday.
     *
     * If the ending day is omitted, then all days will be returned, but with the start day listed first.
     * For example, `getRange(1)` would give you data for Monday-Sunday.
     *
     * @param int $start The first day to return
     * @param int|null $end The last day to return. If null, it will be whatever day comes before `$start`.
     * @return DayData[]
     */
    public function getRange(int $start, ?int $end = null): array
    {
        if ($end === null) {
            $end = $start === 0 ? 6 : $start - 1;
        }

        $data = (array)$this;

        if ($end >= $start) {
            return array_slice($data, $start, $end - $start + 1);
        }

        return array_merge(
            array_slice($data, $start),
            array_slice($data, 0, $end + 1)
        );
    }

    /**
     * Returns a range of the days, grouped by consecutive days that have the same time slot values.
     *
     * @param int $start The first day to return
     * @param int|null $end The last day to return. If null, it will be whatever day comes before `$start`.
     * @return DayData[][]
     */
    public function getGroupedRanges(int $start, ?int $end = null): array
    {
        $range = $this->getRange($start, $end);
        $groups = [];
        $lastSlotKey = null;
        foreach ($range as $day) {
            $slotKey = implode('-', array_map(fn(?DateTime $slot) => $slot ? $this->minuteOfDay($slot) : 'null', (array)$day));
            if ($slotKey !== $lastSlotKey) {
                $groups[] = [];
                $lastSlotKey = $slotKey;
            }
            $groups[count($groups) - 1][] = $day;
        }
        return $groups;
    }

    /**
     * Returns whether any day has any time slots filled in.
     *
     * @return bool
     */
    public function getIsAllBlank(): bool
    {
        foreach ($this as $day) {
            if (!$day->getIsBlank()) {
                return false;
            }
        }

        return true;
    }
}
