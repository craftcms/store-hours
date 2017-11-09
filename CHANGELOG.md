Changelog
=========

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
