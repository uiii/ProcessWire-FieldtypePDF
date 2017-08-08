## 1.1.4 (2017-08-08)

### Fixed
- Fixed module upgrading on PW 3.x [[issue #12](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/12)]

## 1.1.3 (2017-04-19)

### Changed
- Use [Tense](https://github.com/uiii/tense) for testing against multiple versions of ProcessWire

## 1.1.2 (2016-12-09)

### Added
- ProcessWire 3.x support
- Module is installable via Composer

### Changed
- Use [PW-Test](https://github.com/uiii/pw-test) for testing against multiple versions of ProcessWire

## 1.1.1 (2016-08-26)

### Fixed
- Fixed module's installation by classname

## 1.1.0 (2016-08-26)

### Added
- PDF to image converter is now configurable in admin [[issue #7](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/7)]
- You can specify PDF's page number to generate thumbnail [[issue #3](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/3)]
- Fix bugs [[issue #4](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/4), [issue #6](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/6)]
- Add ApiGen config for API documentation generation
- Add PHPUnit tests
- Add license (MIT)

### Deprecated
- Deprecated `thumbnail` method, use `toImage` instead.
- Deprecated `isThumbnail` method, use `isImageOfThis` instead.
- Deprecated `removeThumbnails` method, use `removeImages` instead.

## 1.0.1 (2014-05-29)

### Added
- Added module requirements check [[issue #2](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/2)]
- Set important ImageMagick settings before conversion [[issue #1](https://github.com/uiii/ProcessWire-FieldtypePDF/issues/1)]