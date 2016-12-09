# Change Log

## 3.0.0

### Changed

- Require elasticsearch/elasticsearch 2.X OR 5.X. Bumped to a new major version
  to avoid surprising anyone who is using the bundle.

### Fixed

n/a

## 2.0.0

### Changed

- Require elasticsearch/elasticsearch 2.X

### Fixed

n/a

## 1.0.3

### Changed

- `ElasticsearchFactory` is no longer final

### Fixed

- Don't ignore the logging parameter in configuration

## 1.0.2

### Changed

- Add a `.gitattributes` file to exclude things from exports (eg. composer)
- Add this changelog

## 1.0.1

### Fixed

- Actually use the `hosts` argument in configuration
