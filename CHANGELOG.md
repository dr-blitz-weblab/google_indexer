# Changelog

## [2.2.1] - 2024-09-23

### Fixed

- Resolved issue where the site ID was not found during page deletion.

## [2.2.0] - 2024-09-10

### Added

- Support for Multiple Google API Configuration Files: Now you can provide different Google API configuration files for each site.

### Changed

- Configuration Update: The configuration file now needs to be specified in the site configuration for each individual site.

### Removed

- The config_file_path option can no longer be set in the general settings or extension configuration. All configuration must now be managed at the site level.
