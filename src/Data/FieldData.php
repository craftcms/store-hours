<?php

declare(strict_types=1);

namespace CraftCms\StoreHours\Data;

use ArrayObject;
use CraftCms\Cms\Cms;
use DateTimeInterface;
use LogicException;

/**
 * A Store Hours field value: seven days of time slots, indexed 0 (Sunday) to 6 (Saturday).
 *
 * @extends ArrayObject<int, DayData>
 */
class FieldData extends ArrayObject
{
    /**
     * Returns Sunday’s hours.
     */
    public function getSun(): DayData
    {
        return $this[0];
    }

    /**
     * Returns Monday’s hours.
     */
    public function getMon(): DayData
    {
        return $this[1];
    }

    /**
     * Returns Tuesday’s hours.
     */
    public function getTue(): DayData
    {
        return $this[2];
    }

    /**
     * Returns Wednesday’s hours.
     */
    public function getWed(): DayData
    {
        return $this[3];
    }

    /**
     * Returns Thursday’s hours.
     */
    public function getThu(): DayData
    {
        return $this[4];
    }

    /**
     * Returns Friday’s hours.
     */
    public function getFri(): DayData
    {
        return $this[5];
    }

    /**
     * Returns Saturday’s hours.
     */
    public function getSat(): DayData
    {
        return $this[6];
    }

    /**
     * Returns today’s hours.
     */
    public function getToday(): DayData
    {
        return $this->hoursByDate(now(Cms::timezone()));
    }

    /**
     * Returns yesterday’s hours.
     */
    public function getYesterday(): DayData
    {
        return $this->hoursByDate(now(Cms::timezone())->subDay());
    }

    /**
     * Returns tomorrow’s hours.
     */
    public function getTomorrow(): DayData
    {
        return $this->hoursByDate(now(Cms::timezone())->addDay());
    }

    /**
     * Returns whether we are currently within open hours.
     *
     * @throws LogicException if the field doesn’t define `open` and `close` time slots
     *
     * @since 4.1.0
     */
    public function getIsOpen(): bool
    {
        $today = $this->getToday();

        if (! $today->offsetExists('open') || ! $today->offsetExists('close')) {
            throw new LogicException('Calling getIsOpen() on Store Hours field data is only allowed for fields that define `open` and `close` time slots.');
        }

        $open = $today['open'];
        $close = $today['close'];

        if (! $open || ! $close) {
            return false;
        }

        $now = $this->minuteOfDay(now(Cms::timezone()));

        return $now >= $this->minuteOfDay($open) && $now < $this->minuteOfDay($close);
    }

    private function minuteOfDay(DateTimeInterface $date): int
    {
        return (int) $date->format('G') * 60 + (int) $date->format('i');
    }

    private function hoursByDate(DateTimeInterface $date): DayData
    {
        return $this[(int) $date->format('w')];
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
     * @param  int  $start  The first day to return
     * @param  int|null  $end  The last day to return. If null, it will be whatever day comes before `$start`.
     * @return list<DayData>
     */
    public function getRange(int $start, ?int $end = null): array
    {
        $end ??= $start === 0 ? 6 : $start - 1;
        $data = $this->getArrayCopy();

        if ($end >= $start) {
            return array_slice($data, $start, $end - $start + 1);
        }

        return array_merge(
            array_slice($data, $start),
            array_slice($data, 0, $end + 1),
        );
    }

    /**
     * Returns a range of the days, grouped by consecutive days that have the same time slot values.
     *
     * @param  int  $start  The first day to return
     * @param  int|null  $end  The last day to return. If null, it will be whatever day comes before `$start`.
     * @return list<list<DayData>>
     */
    public function getGroupedRanges(int $start, ?int $end = null): array
    {
        $groups = [];
        $lastSlotKey = null;

        foreach ($this->getRange($start, $end) as $day) {
            $slotKey = implode('-', array_map(
                fn (?DateTimeInterface $slot) => $slot ? $this->minuteOfDay($slot) : 'null',
                $day->getArrayCopy(),
            ));

            if ($slotKey !== $lastSlotKey) {
                $groups[] = [];
                $lastSlotKey = $slotKey;
            }

            $groups[count($groups) - 1][] = $day;
        }

        return $groups;
    }

    /**
     * Returns whether every day has all of its time slots blank.
     */
    public function getIsAllBlank(): bool
    {
        foreach ($this as $day) {
            if (! $day->getIsBlank()) {
                return false;
            }
        }

        return true;
    }
}
