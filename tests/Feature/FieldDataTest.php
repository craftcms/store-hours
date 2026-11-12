<?php

declare(strict_types=1);

use CraftCms\Cms\Cms;
use CraftCms\StoreHours\Data\DayData;
use CraftCms\StoreHours\Data\FieldData;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => Cms::config()->timezone = 'Europe/Brussels');
afterEach(fn () => Date::setTestNow());

function weekdayHours(string $open, string $close): FieldData
{
    $time = fn (string $time) => new DateTime("2026-01-01 $time", new DateTimeZone('Europe/Brussels'));

    return new FieldData(array_map(
        fn (int $day) => new DayData($day, $day === 0 ? ['open' => null, 'close' => null] : ['open' => $time($day === 6 ? '09:00' : $open), 'close' => $time($close)]),
        range(0, 6),
    ));
}

it('is open from the opening minute until the closing minute', function (string $now, bool $isOpen) {
    Date::setTestNow(new DateTime("2026-11-10 $now", new DateTimeZone('Europe/Brussels')));

    expect(weekdayHours('09:30', '17:15')->getIsOpen())->toBe($isOpen);
})->with([
    'before opening, in the opening hour' => ['09:10', false],
    'at opening' => ['09:30', true],
    'before closing, in the closing hour' => ['17:05', true],
    'at closing' => ['17:15', false],
]);

it('groups consecutive days with the same hours and minutes', function () {
    $groups = weekdayHours('09:30', '17:00')->getGroupedRanges(1);

    expect(array_map(fn (array $group) => array_map(fn (DayData $day) => $day->dayIndex, $group), $groups))
        ->toBe([[1, 2, 3, 4, 5], [6], [0]]);
});

it('returns ranges that wrap around the end of the week', function () {
    expect(array_map(fn (DayData $day) => $day->dayIndex, weekdayHours('09:00', '17:00')->getRange(5, 1)))
        ->toBe([5, 6, 0, 1]);
});
