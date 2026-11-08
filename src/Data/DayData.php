<?php

declare(strict_types=1);

namespace CraftCms\StoreHours\Data;

use ArrayObject;
use CraftCms\Cms\Support\Facades\I18N;
use DateTimeInterface;
use OutOfBoundsException;

/**
 * A day’s time slot values, indexed by both slot ID and slot handle.
 *
 * @extends ArrayObject<string, DateTimeInterface|null>
 */
class DayData extends ArrayObject
{
    /**
     * @param  int  $dayIndex  The day index (0–6, Sunday first)
     * @param  array<string, DateTimeInterface|null>  $input
     */
    public function __construct(
        public int $dayIndex,
        array $input,
    ) {
        parent::__construct($input);
    }

    /**
     * Allows time slots to be accessed as properties from PHP, such as `$day->open`.
     *
     * @throws OutOfBoundsException if the day has no such time slot
     */
    public function __get(string $name): ?DateTimeInterface
    {
        if ($this->offsetExists($name)) {
            return $this[$name];
        }

        throw new OutOfBoundsException("Undefined time slot: $name");
    }

    /**
     * Returns the day name.
     *
     * @param  string|null  $length  The format length that should be returned. Values: `\CraftCms\Cms\Translation\Locale::LENGTH_ABBREVIATED`, `::SHORT`, `::MEDIUM`, `::FULL`
     */
    public function getName(?string $length = null): string
    {
        return I18N::getLocale()->getWeekDayName($this->dayIndex, $length);
    }

    /**
     * Returns whether the day has no time slots filled in.
     */
    public function getIsBlank(): bool
    {
        foreach ($this as $slot) {
            if ($slot !== null) {
                return false;
            }
        }

        return true;
    }
}
