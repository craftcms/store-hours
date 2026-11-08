<?php

declare(strict_types=1);

use CraftCms\Cms\Database\Migration;
use CraftCms\Cms\Database\Table;
use CraftCms\Cms\ProjectConfig\ProjectConfig;
use CraftCms\StoreHours\Fields\StoreHours;
use Illuminate\Support\Facades\DB;

/**
 * Points Store Hours fields at the field class’s new name.
 */
return new class extends Migration
{
    private const string OLD_TYPE = 'craft\storehours\Field';

    public function up(): void
    {
        DB::table(Table::FIELDS)
            ->where('type', self::OLD_TYPE)
            ->update(['type' => StoreHours::class]);

        $projectConfig = app(ProjectConfig::class);
        $muteEvents = $projectConfig->muteEvents;
        $projectConfig->muteEvents = true;

        try {
            $fieldConfigs = $projectConfig->find(fn (array $item) => ($item['type'] ?? null) === self::OLD_TYPE);

            foreach (array_keys($fieldConfigs) as $path) {
                $projectConfig->set("$path.type", StoreHours::class);
            }
        } finally {
            $projectConfig->muteEvents = $muteEvents;
        }
    }
};
