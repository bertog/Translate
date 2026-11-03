# Changelog

Changes to `Translate` so far.

## Version 1.4.0

- Added support for Laravel 12 and 13 (Laravel 9, 10 and 11 are still supported).
- Translatable attributes read the eager loaded `translations` relation when available,
  so `Model::with('translations')` no longer runs one query per attribute access.
- Writes made through the trait (`updateOrCreateTranslation`, `storeMultipleTranslations`)
  reset an already loaded `translations` relation, so subsequent reads are up to date.
- Fixed an infinite recursion on models that use the trait without declaring `$translatable`.
- `updateOrCreateTranslation()` now handles more than one field at once
  (single field calls keep the same return values).
- `storeMultipleTranslations()` validates every field before writing and runs in a transaction,
  so an invalid field no longer leaves partially saved translations.
- Fixed `Translation::translatable()` relation (now a `morphTo`).
- Test suite updated for PHPUnit 9 to 12 and Orchestra Testbench 7 to 11.

## Version 1.2.1

- Performance Fixes.


## Version 1.2.0

- The multiple translations method now update the translation if exists.
- Added an alias method createOrUpdateMultipleTranslations()

## Version 1.1.0

- Added a method to store multiple translations at once

## Version 1.0.1
- Changed the toArray() method in the Translatable Trait in order to reduce the query generated and improving performance.


## Version 1.0.0

### Added
- Everything! This is the first release
