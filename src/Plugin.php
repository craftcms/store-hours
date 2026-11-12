<?php

declare(strict_types=1);

namespace CraftCms\StoreHours;

use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\StoreHours\Fields\StoreHours;

/**
 * Store Hours plugin.
 *
 * @since 2.0
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '2.0.0';

    public string $minVersionRequired = '4.0.0';

    protected array $fieldTypes = [
        StoreHours::class,
    ];
}
