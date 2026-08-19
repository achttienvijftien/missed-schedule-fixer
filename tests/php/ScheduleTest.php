<?php
/**
 * Class ScheduleTest
 *
 * @package AchttienVijftien\Plugin\MissedScheduleFixer\Test
 */

namespace AchttienVijftien\Plugin\MissedScheduleFixer\Test;

use AchttienVijftien\Plugin\MissedScheduleFixer\Schedule;
use WP_UnitTestCase;

/**
 * Test the scheduled post fixer.
 */
class ScheduleTest extends WP_UnitTestCase {

	/**
	 * Creates a scheduled post that lost its publish event, like a real missed schedule.
	 *
	 * wp_insert_post() turns a future post with a past date into a published post, so the post is created
	 * with a date far into the future and backdated directly in the database afterwards.
	 *
	 * @param int $offset Number of seconds relative to now to schedule the post at.
	 *
	 * @return int
	 */
	private function create_missed_post( int $offset ): int {
		global $wpdb;

		$post_id = self::factory()->post->create(
			[
				'post_status' => 'future',
				'post_date'   => wp_date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
			]
		);

		$wpdb->update(
			$wpdb->posts,
			[
				'post_status'   => 'future',
				'post_date'     => wp_date( 'Y-m-d H:i:s', time() + $offset ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() + $offset ),
			],
			[ 'ID' => $post_id ]
		);

		clean_post_cache( $post_id );
		wp_clear_scheduled_hook( 'publish_future_post', [ $post_id ] );

		return $post_id;
	}

	/**
	 * Test that only posts within the date window are published.
	 */
	public function test_only_publishes_posts_within_date_window() {
		$recent = $this->create_missed_post( - 2 * HOUR_IN_SECONDS );
		$stale  = $this->create_missed_post( - 2 * DAY_IN_SECONDS );
		$future = $this->create_missed_post( DAY_IN_SECONDS );

		do_action( Schedule::EVENT_HOOK );

		$this->assertSame( 'publish', get_post_status( $recent ), 'Recently missed post should be published.' );
		$this->assertSame( 'future', get_post_status( $stale ), 'Stale missed post should be left alone.' );
		$this->assertSame( 'future', get_post_status( $future ), 'Post outside the window should be left alone.' );
	}

	/**
	 * Test that the query arguments can be widened through the filter.
	 *
	 * Dropping the date query restores the unbounded behaviour, which also proves the window in the test
	 * above is what keeps stale posts out.
	 */
	public function test_query_args_filter_can_widen_the_date_window() {
		add_filter(
			'achttienvijftien_missed_schedule_fixer_query_args',
			function ( $query_args ) {
				unset( $query_args['date_query'] );

				return $query_args;
			}
		);

		$stale = $this->create_missed_post( - 2 * DAY_IN_SECONDS );

		do_action( Schedule::EVENT_HOOK );

		$this->assertSame( 'publish', get_post_status( $stale ), 'Without a date query, stale posts are published.' );
	}
}
