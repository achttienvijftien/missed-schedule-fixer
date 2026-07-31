<?php
/**
 * Bootstraps plugin.
 *
 * @package AchttienVijftien\Plugin\MissedScheduleFixer
 */

namespace AchttienVijftien\Plugin\MissedScheduleFixer;

/**
 * Class Schedule.
 */
class Schedule {

	/**
	 * Event hook name.
	 */
	public const EVENT_HOOK = '1815_missed_schedule_fixer';

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Get (singleton) instance.
	 *
	 * @return $this
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Schedule constructor.
	 */
	private function __construct() {
		// phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
		add_filter( 'cron_schedules', [ $this, 'add_schedule_occurrence' ] );
		add_action( 'init', [ $this, 'schedule_event' ] );
		add_action( self::EVENT_HOOK, [ $this, 'fix_scheduled_posts' ] );
	}

	/**
	 * Adds minutely schedule occurrence.
	 *
	 * @param array $schedules All schedules.
	 *
	 * @return array
	 */
	public function add_schedule_occurrence( $schedules ) {
		if ( ! is_array( $schedules ) ) {
			return $schedules;
		}

		if ( ! empty( $schedules['minutely'] ) ) {
			return $schedules;
		}

		$schedules['minutely'] = [
			'interval' => MINUTE_IN_SECONDS,
			'display'  => __( 'Every minute', 'missed-schedule-fixer' ),
		];

		return $schedules;
	}

	/**
	 * Schedules event that fixes scheduled posts.
	 *
	 * @return void
	 */
	public function schedule_event(): void {
		if ( wp_next_scheduled( self::EVENT_HOOK ) ) {
			return;
		}

		wp_schedule_event( time(), 'minutely', self::EVENT_HOOK );
	}

	/**
	 * Gets scheduled posts within the configured date window.
	 *
	 * @return array|null
	 */
	private function get_scheduled_posts(): ?array {
		$now = time();

		/**
		 * Filters the query arguments used to find scheduled posts.
		 *
		 * @param array $query_args Arguments passed to get_posts().
		 */
		$query_args = apply_filters(
			'achttienvijftien_missed_schedule_fixer_query_args',
			[
				'post_type'      => 'any',
				'post_status'    => 'future',
				'posts_per_page' => - 1,
				'orderby'        => 'none',
				'date_query'     => [
					[
						'column'    => 'post_date',
						'after'     => wp_date( 'Y-m-d H:i:s', $now - DAY_IN_SECONDS ),
						'before'    => wp_date( 'Y-m-d H:i:s', $now + HOUR_IN_SECONDS ),
						'inclusive' => true,
					],
				],
			]
		);

		$scheduled_posts = get_posts( $query_args );

		return $scheduled_posts ?: null;
	}

	/**
	 * Fixes scheduled posts.
	 *
	 * @return void
	 */
	public function fix_scheduled_posts(): void {
		$scheduled_posts = $this->get_scheduled_posts();

		if ( ! $scheduled_posts ) {
			return;
		}

		foreach ( $scheduled_posts as $scheduled_post ) {
			if ( wp_get_scheduled_event( 'publish_future_post', [ $scheduled_post->ID ] ) ) {
				continue;
			}

			check_and_publish_future_post( $scheduled_post );
		}
	}
}
