<?php

declare(strict_types=1);

use CraftCms\Cms\Database\Table;
use CraftCms\Cms\Field\Models\Field as FieldModel;
use CraftCms\Cms\ProjectConfig\ProjectConfig;
use CraftCms\StoreHours\Fields\StoreHours;
use Illuminate\Support\Facades\DB;

it('points Store Hours 4 fields at the new field class', function () {
    $field = FieldModel::factory()->create(['handle' => 'hours', 'type' => 'craft\storehours\Field']);
    $projectConfig = app(ProjectConfig::class);
    $projectConfig->muteEvents = true;
    $projectConfig->set("fields.$field->uid", ['name' => 'Hours', 'handle' => 'hours', 'type' => 'craft\storehours\Field']);
    $projectConfig->muteEvents = false;

    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_11_08_000000_update_store_hours_field_type.php';
    $migration->up();

    expect(DB::table(Table::FIELDS)->where('id', $field->id)->value('type'))->toBe(StoreHours::class)
        ->and($projectConfig->get("fields.$field->uid.type"))->toBe(StoreHours::class);
});
