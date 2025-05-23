# SiteOrigin Installer GitHub Updater

## Overview
The GitHub Updater is responsible for checking for updates to the SiteOrigin Installer plugin from the GitHub repository. It ensures that users have access to the latest features and fixes.

## Installation
The updater is included as part of the SiteOrigin Installer plugin. No additional installation steps are required.

## Usage
The updater checks for updates by comparing the version in the plugin header of the `siteorigin-installer.php` file on the `master` branch of the GitHub repository with the local `SITEORIGIN_INSTALLER_VERSION` constant. If the version on GitHub is newer, it triggers an update.

## Configuration
- **Branch:** The updater uses the `master` branch to check for updates. This can be configured by changing the `UPDATES_BRANCH` constant in the updater class.

## Troubleshooting
- Ensure that the plugin version is correctly set in the `siteorigin-installer.php` file to avoid update issues.
- Check network connectivity if updates are not being detected.

## License
This updater is distributed under the GPL3 license. See the LICENSE file for more details. 