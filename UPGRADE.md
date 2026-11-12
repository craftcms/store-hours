# Upgrading to Store Hours 5.0 (Craft 6)

Store Hours 5.0 is a native Craft 6 plugin. This guide lists what changed from 4.x and what to update in your project.

## Requirements

- Craft CMS 6.0 or later
- PHP 8.5 or later
- The `craftcms/yii2-adapter` package is not needed.

## Upgrade steps

1. Upgrade your project to Craft 6 first. Store Hours 4.x runs on Craft 6 through `craftcms/yii2-adapter`, so you can upgrade Craft before the plugin.
2. Update the plugin: `composer require craftcms/store-hours:^5.0`.
3. Run `php artisan craft:up`. A migration points your existing Store Hours fields at the new field class, in the database and in project config.

Field settings and saved field values are kept as they are. Templates that output Store Hours values keep working.

## Breaking changes

### Classes and namespaces

| Before | After |
| --- | --- |
| `craft\storehours\Plugin` | `CraftCms\StoreHours\Plugin` |
| `craft\storehours\Field` | `CraftCms\StoreHours\Fields\StoreHours` |
| `craft\storehours\data\DayData` | `CraftCms\StoreHours\Data\DayData` |
| `craft\storehours\data\FieldData` | `CraftCms\StoreHours\Data\FieldData` |
| `craft\storehours\gql\types\Day` | `CraftCms\StoreHours\Gql\Types\Day` |
| `craft\storehours\gql\types\generators\DayType` | `CraftCms\StoreHours\Gql\Types\Generators\DayType` |

Update any PHP code that checks a field’s class:

```php
// Before
if ($field instanceof \craft\storehours\Field) {
    // ...
}

// After
if ($field instanceof \CraftCms\StoreHours\Fields\StoreHours) {
    // ...
}
```

Fields defined in `config/project/` YAML are updated by the migration in step 3.

### Exceptions

- Reading a time slot the field doesn’t have from PHP (`$day->foo`) throws `OutOfBoundsException` in place of `yii\base\UnknownPropertyException`.
- `FieldData::getIsOpen()` throws `LogicException` in place of `yii\base\InvalidConfigException` when the field has no `open` and `close` time slots.

### Field settings

- `StoreHours::$slots` is an array of `name` and `handle` pairs indexed by slot ID, as before, and is no longer `null` before the field is initialized.
- The field settings and input use Craft 6’s settings UI and table control, in place of `getSettingsHtml()`, `inputHtml()` and `getStaticHtml()`.

## Behavior changes

- `FieldData::getIsOpen()` and `getGroupedRanges()` now take minutes into account. In 4.x they compared hours only, so a store opening at 9:30 was reported as open at 9:10, and 9:00–17:00 and 9:30–17:00 days were grouped together.
- GraphQL mutations can set time slots by handle as well as by slot ID.
