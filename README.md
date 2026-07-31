# Missed Schedule Fixer

Ever had problems with scheduled posts not being published? This plugin fixes that problem.

## Configuration

Every minute the plugin looks for scheduled posts that lost their publish event. To keep that query fast,
only posts scheduled within a window around the current time are considered: by default from one day into
the past up to one hour into the future. Posts that were scheduled long before that window are considered
stale and are left alone.

Use the `achttienvijftien_missed_schedule_fixer_query_args` filter to change the query. For example, to
look a week into the past instead of a day:

```php
add_filter(
	'achttienvijftien_missed_schedule_fixer_query_args',
	function ( $query_args ) {
		$query_args['date_query'][0]['after'] = wp_date( 'Y-m-d H:i:s', time() - WEEK_IN_SECONDS );

		return $query_args;
	}
);
```

Keep the query bounded by `post_date`. Removing the date query brings back the slow, unbounded behaviour
this window is meant to avoid.

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
