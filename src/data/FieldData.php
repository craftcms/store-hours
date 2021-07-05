<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\storehours\data;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
    /**
     * Returns today’s hours.
     *
     * @return DayData
     */
    public function getToday(): DayData
    {
        $today = (int)(new \DateTime())->format('w');
        return $this[$today];
    }
    /**
     * Returns tomorrows’s hours.
     *
     * @return TomorrowData
     */
    public function getTomorrow(): DayData
    {
        $tomorrow = (int)(new \DateTime())->format('w');
        return $this[$tomorrow +1];
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
    public function getRange(int $start, int $end = null): array
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
