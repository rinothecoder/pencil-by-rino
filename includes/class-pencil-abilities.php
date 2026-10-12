<?php
/**
 * Pencilino abilities, so AI agents can read client activity and feedback through MCP.
 *
 * Deliberately read and resolve only. Agents change the theme itself, so the
 * activity log keeps showing what people did in the frontend editor.
 *
 * @package Pencilino
 */

defined( 'ABSPATH' ) || exit;

final class Pencil_Abilities {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Same audience as the editor and the comment REST routes.
	 *
	 * @return bool
	 */
	public static function can_view() {
		return Pencil_Plugin::can_edit();
	}

	/**
	 * Same capability as the REST route that resolves comments.
	 *
	 * @return bool
	 */
	public static function can_resolve() {
		return current_user_can( 'manage_options' );
	}

	private static function meta( $readonly, $idempotent ) {
		return array(
			'public'       => true,
			'show_in_rest' => true,
			'mcp'          => array( 'public' => true ),
			'annotations'  => array(
				'readonly'    => $readonly,
				'destructive' => false,
				'idempotent'  => $idempotent,
			),
		);
	}

	private static function add( $name, $label, $description, $properties, $required, $meta, $permission ) {
		$schema = array(
			'type'                 => 'object',
			'properties'           => $properties,
			'additionalProperties' => false,
		);
		if ( $required ) {
			$schema['required'] = $required;
		} else {
			$schema['default'] = array();
		}
		wp_register_ability(
			'pencilino/' . $name,
			array(
				'label'               => 'Pencilino: ' . $label,
				'description'         => $description,
				'category'            => 'site',
				'input_schema'        => $schema,
				'output_schema'       => array( 'type' => 'object' ),
				'execute_callback'    => array( __CLASS__, str_replace( '-', '_', $name ) ),
				'permission_callback' => array( __CLASS__, $permission ),
				'meta'                => $meta,
			)
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}
		self::add(
			'list-changes',
			'List Changes',
			'List the newest content edits made through the Pencilino frontend editor, with the field, the old and new value, who made the edit, and the page. Image values are attachment IDs and button values are JSON with text and url. Times are UTC. Edits an agent makes to the theme are not part of this log. Requires an administrator or editor.',
			array( 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 200, 'default' => 50, 'description' => 'How many of the newest changes to return.' ) ),
			array(),
			self::meta( true, true ),
			'can_view'
		);
		self::add(
			'list-comments',
			'List Comments',
			'List the private element comments that people left on the frontend, 50 per page. Each comment has its page, the comment text, the author and a target: selector, Pencilino field ID, element ID, tag, label, a short text excerpt, and the x and y position of the pin (0 to 1). Check the target data to find the element a comment is about. Requires an administrator or editor.',
			array(
				'status' => array( 'type' => 'string', 'enum' => array( 'open', 'resolved' ), 'default' => 'open', 'description' => 'open (default) or resolved.' ),
				'page'   => array( 'type' => 'integer', 'minimum' => 1, 'default' => 1 ),
			),
			array(),
			self::meta( true, true ),
			'can_view'
		);
		self::add(
			'resolve-comment',
			'Resolve Comment',
			'Mark a comment as resolved, or reopen it with resolved set to false. Only the status changes, never the comment itself. Requires manage_options.',
			array(
				'id'       => array( 'type' => 'integer', 'minimum' => 1, 'description' => 'Comment ID, as returned by list-comments.' ),
				'resolved' => array( 'type' => 'boolean', 'default' => true ),
			),
			array( 'id' ),
			self::meta( false, true ),
			'can_resolve'
		);
	}

	public static function list_changes( $input ) {
		$rows    = Pencil_Activity::get_recent( isset( $input['limit'] ) ? (int) $input['limit'] : 50 );
		$changes = array();
		foreach ( $rows as $row ) {
			$url       = $row->page_id ? get_permalink( (int) $row->page_id ) : '';
			$changes[] = array(
				'id'             => (int) $row->activity_id,
				'field_id'       => $row->field_id,
				'field_label'    => $row->field_label,
				'field_type'     => $row->field_type,
				'old_value'      => $row->old_value,
				'new_value'      => $row->new_value,
				'user_id'        => (int) $row->user_id,
				'user_name'      => $row->user_name,
				'page_id'        => (int) $row->page_id,
				'page_url'       => $url ? $url : '',
				'changed_at_utc' => $row->created_at,
			);
		}
		return array(
			'count'   => count( $changes ),
			'changes' => $changes,
		);
	}

	public static function list_comments( $input ) {
		$resolved = isset( $input['status'] ) && 'resolved' === $input['status'];
		$page     = isset( $input['page'] ) ? max( 1, (int) $input['page'] ) : 1;
		$rows     = Pencil_Comments::inbox( $resolved, $page );
		$has_more = count( $rows ) > 50;
		$comments = array();
		foreach ( array_slice( $rows, 0, 50 ) as $row ) {
			$item                    = Pencil_Comments::serialise( $row );
			$item['page_id']         = (int) $row->page_id;
			$item['resolved_at_utc'] = $row->resolved_at;
			$comments[]              = $item;
		}
		return array(
			'status'   => $resolved ? 'resolved' : 'open',
			'page'     => $page,
			'has_more' => $has_more,
			'comments' => $comments,
		);
	}

	public static function resolve_comment( $input ) {
		$id       = absint( $input['id'] );
		$resolved = isset( $input['resolved'] ) ? (bool) $input['resolved'] : true;
		$result   = Pencil_Comments::set_resolved( $id, $resolved );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return array(
			'id'       => $id,
			'resolved' => $resolved,
		);
	}
}
