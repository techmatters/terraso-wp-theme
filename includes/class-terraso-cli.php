<?php
/**
 * WPCLI Utilities for Terraso
 *
 * @package Terraso
 */

/**
 * Holds methods for WP_CLI command related to Terraso
 * Class Terraso_CLI
 */
class Terraso_CLI extends WP_CLI_Command {

	const POST_TYPE = 'guide';

	/**
	 * Insert the outputs in to the database.
	 *
	 * @param string $element           THe ILM element these outputs belong to.
	 * @param string $output_data       Array of element titles and contents.
	 * @param string $dry_run           If performing a try run.
	 */
	public static function import_outputs( $element, $output_data, $dry_run ) {

		$post_parent = get_page_by_title( $element, OBJECT, self::POST_TYPE );

		if ( ! $post_parent ) {
			WP_CLI::error( "Could not find post for element {$element}" );
		}

		foreach ( $output_data as $item ) {
			WP_CLI::line( "Creating output {$item['title']}" );

			$post_data = [
				'post_title'   => $item['title'],
				'post_content' => $item['contents'],
				'post_status'  => 'publish',
				'post_type'    => self::POST_TYPE,
				'post_parent'  => $post_parent->ID,
				'tax_input'    => [
					'ilm_type' => [ 'ilm-output' ],
				],
			];

			if ( $dry_run ) {
				WP_CLI::line( wp_json_encode( $post_data ) );
			} else {
				$result = wp_insert_post( $post_data );
				if ( is_wp_error( $result ) ) {
					WP_CLI::error( "Failed to create output. Error: {$result->messae}" );
				}
			}
		}
	}


	/**
	 * Read in CSV data and return an array.
	 *
	 * @param string $csv_file           Path to a CSV file.
	 */
	public static function get_csv_data( $csv_file ) {
		if ( ! $csv_file ) {
			return;
		}

		$handle = fopen( $csv_file, 'r' );
		if ( false === $handle ) {
			return;
		}

		$result  = [];
		$counter = 0;
		do {
			$data = fgetcsv( $handle );

			// Skip header row from CSV.
			if ( 0 === $counter++ ) {
				continue;
			}

			list( $title, $contents, $element ) = $data;
			if ( ! $element ) {
				continue;
			}

			if ( ! isset( $result[ $element ] ) ) {
				$result[ $element ] = [];
			}
			if ( $title && $contents ) {
				$result[ $element ][] = [
					'title'    => trim( $title ),
					'contents' => trim( $contents ),
				];
			}
		} while ( false !== $data );

		fclose( $handle );

		return $result;
	}

	/**
	 * Import ILM Guide data from CSV
	 *
	 * ## EXAMPLES
	 *
	 *   wp terraso import-ilm-outputs --file=<file>
	 *   wp terraso import-ilm-outputs --file=<file> --dry-run
	 *
	 * @synposis --file=<file> --dry-run
	 *
	 * @subcommand import-ilm-outputs
	 * @param string $args           CLI arguments.
	 * @param string $assoc_args     CLI arguments, associative array.
	 */
	public function import_ilm_outputs( $args, $assoc_args ) {
		if ( empty( $assoc_args['file'] ) ) {
			WP_CLI::error( 'Specify a file to import from.' );
		}

		if ( isset( $assoc_args['dry-run'] ) && 'false' === $assoc_args['dry-run'] ) {
			$dry_run = false;
		} else {
			$dry_run = true;
			WP_CLI::line( '!!! Doing a dry-run, no posts will be created.' );
		}

		$import_file = $assoc_args['file'];
		if ( ! file_exists( $import_file ) ) {
			WP_CLI::error( "File {$import_file} does not exist." );
		}

		$data = self::get_csv_data( $import_file );

		$item_count = 0;
		foreach ( $data as $element => $output_data ) {
			WP_CLI::line( "Found element {$element}." );
			$item_count++;

			self::import_outputs( $element, $output_data, $dry_run );

			if ( 0 === $item_count % 100 ) {
				WP_CLI::line( 'sleeping' );
				sleep( 5 );
			}
		}
	}
}

WP_CLI::add_command( 'terraso', 'Terraso_CLI' );
