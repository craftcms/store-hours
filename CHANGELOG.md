Changelog
=========

## Unreleased

### Added
- Added a new “Time Slots” field setting, which makes it possible to customize the available field columns. ([#22](https://github.com/craftcms/store-hours/issues/22))
- It’s now possible to access _today’s_ hours via `entry.<FieldHandle>.getToday()`. ([#15](https://github.com/craftcms/store-hours/issues/15))
- It’s now possible to output weekday names via `entry.<FieldHandle>[<DayIndex>].getName()`.

## 2.0.6 - 2017-12-04

### Changed
- Loosened the Craft CMS version requirement to allow any 3.x version.

## 2.0.5 - 2017-11-09

### Fixed
- Fixed some bugs.

## 2.0.4 - 2017-07-07

### Changed
- Craft 3 Beta 20 compatibility.

## 2.0.3 - 2017-05-15

### Fixed
- Fixed a deprecation error.

## 2.0.2 - 2017-05-15

### Fixed
- Fixed a bug where the plugin wasn’t updating Store Hours fields that had been created in Craft 2.x.

## 2.0.1 - 2017-05-15

### Added
- Added a Dutch translation. ([#13](https://github.com/craftcms/store-hours/pull/13))

### Changed
- Changed the Store Hours field class from `craft\storehours\fields\StoreHoursField` to `craft\storehours\Field`.

### Fixed
- Fixed an error that occurred when adding a Store Hours field to a global set. ([#14](https://github.com/craftcms/store-hours/pull/14))
- Fixed the changelog and download URLs.

## 2.0.0 - 2017-05-10

### Changed
- Added support for Craft 3.

## 1.2.2 - 2017-11-09

### Fixed
- Fixed a bug where looping through the field data on the front end could return the days out of order, if the user that saved it had a different Week Start Day than Sunday. ([#19](https://github.com/craftcms/store-hours/issues/19))

## 1.2.1 - 2017-04-21

### Fixed
- Fixed a PHP error that occurred if the current user’s Week Start Day setting was set to Sunday. ([#12](https://github.com/craftcms/store-hours/issues/12))

## 1.2.0 - 2017-04-18

### Added
- Store Hours fields now respect users’ Week Start Day settings. ([#11](https://github.com/craftcms/store-hours/issues/11))

## 1.1.0 - 2015-12-20

### Changed
- Updated to take advantage of new Craft 2.5 plugin features.

### Fixed
- Fixed a bug where blank times were being saved as arrays.
- Fixed a bug where times were not reflecting the system timezone. ([#3](https://github.com/craftcms/store-hours/issues/3))

## 1.0.0 - 2014-07-16

- Initial release.
