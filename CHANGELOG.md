# Release Notes for Store Hours

## 4.3.0 - 2026-11-10
- Added support for Craft CMS 6 with the `craftcms/yii2-adapter` package.
- Fixed a PHP error that occurred when editing an element with a Store Hours field whose “Start Day” setting was Tuesday or later, on PHP 8.3 or later.

## 4.2.0 - 2024-07-28
- Added `craft\storehours\data\FieldData::getGroupedRanges()`.

## 4.1.0 - 2024-07-28
- Added the “Start Day” field setting. ([#43](https://github.com/craftcms/store-hours/issues/43))
- Added `craft\storehours\data\FieldData::getIsOpen()`. ([#37](https://github.com/craftcms/store-hours/issues/37))
- Store Hours fields now get clock icons.

## 4.0.1 - 2024-03-18
- Fixed a PHP error that could occur when editing an element with a Store Hours field.

## 4.0.0 - 2024-03-15
- Store Hours now requires Craft 5.0.0-beta.1 or later.

## 3.1.0 - 2024-03-15
- Added GraphQL support. ([#55](https://github.com/craftcms/store-hours/pull/55))
- Added `craft\storehours\gql\types\Day`.
- Added `craft\storehours\gql\types\generators\DayType`.

## 3.0.0 - 2022-05-03
- Added Craft 4 compatibility

## 2.2.0 - 2022-04-10
- Added `craft\storehours\data\FieldData::getSun()`.
- Added `craft\storehours\data\FieldData::getMon()`.
- Added `craft\storehours\data\FieldData::getTue()`.
- Added `craft\storehours\data\FieldData::getWed()`.
- Added `craft\storehours\data\FieldData::getThu()`.
- Added `craft\storehours\data\FieldData::getFri()`.
- Added `craft\storehours\data\FieldData::getSat()`.
- Added `craft\storehours\data\FieldData::getSun()`.
- Added `craft\storehours\data\FieldData::getYesterday()`.
- Added `craft\storehours\data\FieldData::getTomorrow()`. ([#44](https://github.com/craftcms/store-hours/pull/44))

## 2.1.1.1 - 2018-11-01
- Fixed an error if you tried to access a time slot from a template from a custom custom column that had an empty value. ([#31](https://github.com/craftcms/store-hours/issues/31))

## 2.1.1 - 2018-09-03
- It’s now possible to find out if all the time slots for all the days are blank via `entry.<FieldHandle>.getIsAllBlank()`. ([#29](https://github.com/craftcms/store-hours/pull/29))
- Fixed a bug where required Store Hours fields wouldn’t get a validation error if none of the time slots were filled in. 

## 2.1.0.1 - 2018-07-19
- Fixed a bug where older Store Hours data could get lost when upgrading to v2.1.

## 2.1.0 - 2018-07-19
- Added a new “Time Slots” field setting, which makes it possible to customize the available field columns. ([#22](https://github.com/craftcms/store-hours/issues/22))
- It’s now possible to access _today’s_ hours via `entry.<FieldHandle>.getToday()`. ([#15](https://github.com/craftcms/store-hours/issues/15))
- It’s now possible to output weekday names via `entry.<FieldHandle>[<DayIndex>].getName()`.
- It’s now possible to access a custom range of days, or change which days comes first, via `entry.<FieldHandle>.getRange()`.
- It’s now possible to find out if all the time slots for a day are blank via `entry.<FieldHandle>[<DayIndex>].getIsBlank()`.

## 2.0.6 - 2017-12-04
- Loosened the Craft CMS version requirement to allow any 3.x version.

## 2.0.5 - 2017-11-09
- Fixed some bugs.

## 2.0.4 - 2017-07-07
- Craft 3 Beta 20 compatibility.

## 2.0.3 - 2017-05-15
- Fixed a deprecation error.

## 2.0.2 - 2017-05-15
- Fixed a bug where the plugin wasn’t updating Store Hours fields that had been created in Craft 2.x.

## 2.0.1 - 2017-05-15
- Added a Dutch translation. ([#13](https://github.com/craftcms/store-hours/pull/13))
- Changed the Store Hours field class from `craft\storehours\fields\StoreHoursField` to `craft\storehours\Field`.
- Fixed an error that occurred when adding a Store Hours field to a global set. ([#14](https://github.com/craftcms/store-hours/pull/14))
- Fixed the changelog and download URLs.

## 2.0.0 - 2017-05-10
- Added support for Craft 3.

## 1.2.2 - 2017-11-09
- Fixed a bug where looping through the field data on the front end could return the days out of order, if the user that saved it had a different Week Start Day than Sunday. ([#19](https://github.com/craftcms/store-hours/issues/19))

## 1.2.1 - 2017-04-21
- Fixed a PHP error that occurred if the current user’s Week Start Day setting was set to Sunday. ([#12](https://github.com/craftcms/store-hours/issues/12))

## 1.2.0 - 2017-04-18
- Store Hours fields now respect users’ Week Start Day settings. ([#11](https://github.com/craftcms/store-hours/issues/11))

## 1.1.0 - 2015-12-20
- Updated to take advantage of new Craft 2.5 plugin features.
- Fixed a bug where blank times were being saved as arrays.
- Fixed a bug where times were not reflecting the system timezone. ([#3](https://github.com/craftcms/store-hours/issues/3))

## 1.0.0 - 2014-07-16
- Initial release.
