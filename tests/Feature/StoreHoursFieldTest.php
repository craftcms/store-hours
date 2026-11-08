<?php

declare(strict_types=1);

use CraftCms\Cms\Cms;
use CraftCms\Cms\Database\Table;
use CraftCms\Cms\Entry\Elements\Entry;
use CraftCms\Cms\Entry\Models\Entry as EntryModel;
use CraftCms\Cms\Entry\Models\EntryType;
use CraftCms\Cms\Field\FieldContext;
use CraftCms\Cms\Field\Models\Field as FieldModel;
use CraftCms\Cms\Support\Facades\Elements;
use CraftCms\Cms\Support\Facades\EntryTypes;
use CraftCms\Cms\Support\Facades\Fields;
use CraftCms\Cms\Support\Json;
use CraftCms\StoreHours\Data\FieldData;
use CraftCms\StoreHours\Fields\StoreHours;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => Cms::config()->timezone = 'Europe/Brussels');

const SLOTS = [
    'open' => ['name' => 'Opening Time', 'handle' => 'open'],
    'slot3' => ['name' => 'Lunch', 'handle' => 'lunch'],
];

it('saves submitted rows by their day and reads the times back in the system time zone', function () {
    $field = FieldModel::factory()->create(['handle' => 'hours', 'type' => StoreHours::class, 'settings' => ['slots' => SLOTS, 'startDay' => 2]]);
    $entryId = EntryModel::factory()->createElement(['typeId' => EntryType::factory()->withField($field)])->id;
    EntryTypes::refreshEntryTypes();
    Fields::refreshFields();
    $entry = Entry::find()->id($entryId)->one();

    $entry->setFieldValueFromRequest('hours', [
        ['day' => '2', 'open' => '09:30', 'slot3' => '12:15'],
        ['day' => '3', 'open' => '', 'slot3' => ''],
        ['day' => '4', 'open' => '', 'slot3' => ''],
        ['day' => '5', 'open' => '', 'slot3' => ''],
        ['day' => '6', 'open' => '', 'slot3' => ''],
        ['day' => '0', 'open' => '', 'slot3' => ''],
        ['day' => '1', 'open' => '08:00', 'slot3' => ''],
    ]);
    expect(Elements::saveElement($entry))->toBeTrue();

    /** @var FieldData $hours */
    $hours = Entry::find()->id($entry->id)->one()->getFieldValue('hours');
    $stored = Json::decode(DB::table(Table::ELEMENTS_SITES)->where('elementId', $entry->id)->value('content'));

    expect($hours->getTue()->open->format('H:i e'))->toBe('09:30 Europe/Brussels')
        ->and($hours->getTue()->lunch->format('H:i'))->toBe('12:15')
        ->and($hours->getMon()->open->format('H:i'))->toBe('08:00')
        ->and($hours->getSun()->getIsBlank())->toBeTrue()
        ->and(array_values($stored)[0][2]['open'])->toMatch('/^\d{4}-\d{2}-\d{2}T09:30:00\+0[12]:00$/');
});

it('reads values saved by Store Hours 4', function () {
    $field = new StoreHours(['handle' => 'hours', 'slots' => SLOTS]);

    $hours = $field->normalizeValue(Json::encode([
        ['open' => null, 'slot3' => null],
        ['open' => '2024-03-15T09:00:00+01:00', 'slot3' => '2024-03-15T12:30:00+01:00'],
    ]), null);

    expect($hours->getMon()->open->format('H:i'))->toBe('09:00')
        ->and($hours->getMon()->lunch->format('H:i'))->toBe('12:30')
        ->and($hours->getSat()->getIsBlank())->toBeTrue();
});

it('accepts times keyed by slot handle, as GraphQL mutations send them', function () {
    $field = new StoreHours(['handle' => 'hours', 'slots' => SLOTS]);

    $hours = $field->normalizeValue([[], ['lunch' => new DateTime('2026-11-09 12:45', new DateTimeZone('Europe/Brussels'))]], null);

    expect($hours->getMon()['slot3']->format('H:i'))->toBe('12:45');
});

it('lists the days from the field’s start day', function () {
    $field = new StoreHours(['handle' => 'hours', 'slots' => SLOTS, 'startDay' => 3]);

    $rows = $field->uiControl(new FieldContext('hours'))->getValue();

    expect(array_column($rows, 'day'))->toBe(['3', '4', '5', '6', '0', '1', '2'])
        ->and($rows[0]['heading'])->toBe('Wednesday');
});

it('gives new fields opening and closing time slots', function () {
    expect((new StoreHours)->slots)->toBe([
        'open' => ['name' => 'Opening Time', 'handle' => 'open'],
        'close' => ['name' => 'Closing Time', 'handle' => 'close'],
    ]);
});
