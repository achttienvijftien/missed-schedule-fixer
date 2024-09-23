# Missed Schedule Fixer

Ever had problems with scheduled posts not being published? This plugin fixes that problem.

## Prerequisites

### Basic installation

- PHP >= 8.1
- [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

### Testing

- Docker
- [nvm](https://github.com/nvm-sh/nvm#install--update-script)
- [Yarn](https://yarnpkg.com/getting-started/install)

## Testing

### Setup

1. Install Composer packages: `composer install`
2. Install the correct Node.js version: `nvm install`
3. Install NPM packages: `yarn`
4. Start wp-env `yarn wp-env start`
5. Check if test suite is ready: `yarn test`
6. When test result is OK you're ready to start writing tests in test/php
