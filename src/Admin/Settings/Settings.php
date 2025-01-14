<?php

namespace WPGraphQL\Admin\Settings;

/**
 * Class Settings
 *
 * @package WPGraphQL\Admin\Settings
 */
class Settings {

	/**
	 * @var \WPGraphQL\Admin\Settings\SettingsRegistry
	 */
	public $settings_api;

	/**
	 * WP_ENVIRONMENT_TYPE
	 *
	 * @var string The WordPress environment.
	 */
	protected $wp_environment;

	/**
	 * Initialize the WPGraphQL Settings Pages
	 *
	 * @return void
	 */
	public function init() {
		$this->wp_environment = $this->get_wp_environment();
		$this->settings_api   = new SettingsRegistry();

		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'initialize_settings_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'initialize_settings_page_scripts' ] );
	}

	/**
	 * Return the environment. Default to production.
	 *
	 * @return string The environment set using WP_ENVIRONMENT_TYPE.
	 */
	protected function get_wp_environment() {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return wp_get_environment_type();
		}

		return 'production';
	}

	/**
	 * Add the options page to the WP Admin
	 *
	 * @return void
	 */
	public function add_options_page() {

		$graphiql_enabled = get_graphql_setting( 'graphiql_enabled' );

		if ( 'off' === $graphiql_enabled ) {
			add_menu_page(
				__( 'WPGraphQL Settings', 'wp-graphql' ),
				__( 'GraphQL', 'wp-graphql' ),
				'manage_options',
				'graphql-settings',
				[ $this, 'render_settings_page' ],
				'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MDAgNDAwIj48cGF0aCBmaWxsPSIjRTEwMDk4IiBkPSJNNTcuNDY4IDMwMi42NmwtMTQuMzc2LTguMyAxNjAuMTUtMjc3LjM4IDE0LjM3NiA4LjN6Ii8+PHBhdGggZmlsbD0iI0UxMDA5OCIgZD0iTTM5LjggMjcyLjJoMzIwLjN2MTYuNkgzOS44eiIvPjxwYXRoIGZpbGw9IiNFMTAwOTgiIGQ9Ik0yMDYuMzQ4IDM3NC4wMjZsLTE2MC4yMS05Mi41IDguMy0xNC4zNzYgMTYwLjIxIDkyLjV6TTM0NS41MjIgMTMyLjk0N2wtMTYwLjIxLTkyLjUgOC4zLTE0LjM3NiAxNjAuMjEgOTIuNXoiLz48cGF0aCBmaWxsPSIjRTEwMDk4IiBkPSJNNTQuNDgyIDEzMi44ODNsLTguMy0xNC4zNzUgMTYwLjIxLTkyLjUgOC4zIDE0LjM3NnoiLz48cGF0aCBmaWxsPSIjRTEwMDk4IiBkPSJNMzQyLjU2OCAzMDIuNjYzbC0xNjAuMTUtMjc3LjM4IDE0LjM3Ni04LjMgMTYwLjE1IDI3Ny4zOHpNNTIuNSAxMDcuNWgxNi42djE4NUg1Mi41ek0zMzAuOSAxMDcuNWgxNi42djE4NWgtMTYuNnoiLz48cGF0aCBmaWxsPSIjRTEwMDk4IiBkPSJNMjAzLjUyMiAzNjdsLTcuMjUtMTIuNTU4IDEzOS4zNC04MC40NSA3LjI1IDEyLjU1N3oiLz48cGF0aCBmaWxsPSIjRTEwMDk4IiBkPSJNMzY5LjUgMjk3LjljLTkuNiAxNi43LTMxIDIyLjQtNDcuNyAxMi44LTE2LjctOS42LTIyLjQtMzEtMTIuOC00Ny43IDkuNi0xNi43IDMxLTIyLjQgNDcuNy0xMi44IDE2LjggOS43IDIyLjUgMzEgMTIuOCA0Ny43TTkwLjkgMTM3Yy05LjYgMTYuNy0zMSAyMi40LTQ3LjcgMTIuOC0xNi43LTkuNi0yMi40LTMxLTEyLjgtNDcuNyA5LjYtMTYuNyAzMS0yMi40IDQ3LjctMTIuOCAxNi43IDkuNyAyMi40IDMxIDEyLjggNDcuN00zMC41IDI5Ny45Yy05LjYtMTYuNy0zLjktMzggMTIuOC00Ny43IDE2LjctOS42IDM4LTMuOSA0Ny43IDEyLjggOS42IDE2LjcgMy45IDM4LTEyLjggNDcuNy0xNi44IDkuNi0zOC4xIDMuOS00Ny43LTEyLjhNMzA5LjEgMTM3Yy05LjYtMTYuNy0zLjktMzggMTIuOC00Ny43IDE2LjctOS42IDM4LTMuOSA0Ny43IDEyLjggOS42IDE2LjcgMy45IDM4LTEyLjggNDcuNy0xNi43IDkuNi0zOC4xIDMuOS00Ny43LTEyLjhNMjAwIDM5NS44Yy0xOS4zIDAtMzQuOS0xNS42LTM0LjktMzQuOSAwLTE5LjMgMTUuNi0zNC45IDM0LjktMzQuOSAxOS4zIDAgMzQuOSAxNS42IDM0LjkgMzQuOSAwIDE5LjItMTUuNiAzNC45LTM0LjkgMzQuOU0yMDAgNzRjLTE5LjMgMC0zNC45LTE1LjYtMzQuOS0zNC45IDAtMTkuMyAxNS42LTM0LjkgMzQuOS0zNC45IDE5LjMgMCAzNC45IDE1LjYgMzQuOSAzNC45IDAgMTkuMy0xNS42IDM0LjktMzQuOSAzNC45Ii8+PC9zdmc+'
			);

		} else {
			add_submenu_page(
				'graphiql-ide',
				__( 'WPGraphQL Settings', 'wp-graphql' ),
				__( 'Settings', 'wp-graphql' ),
				'manage_options',
				'graphql-settings',
				[ $this, 'render_settings_page' ]
			);
		}

	}

	/**
	 * Registers the initial settings for WPGraphQL
	 *
	 * @return void
	 */
	public function register_settings() {

		$this->settings_api->register_section( 'graphql_general_settings', [
			'title' => __( 'WPGraphQL General Settings', 'wp-graphql' ),
		] );

		$custom_endpoint = apply_filters( 'graphql_endpoint', null );
		$this->settings_api->register_field( 'graphql_general_settings',
			[
				'name'              => 'graphql_endpoint',
				'label'             => __( 'GraphQL Endpoint', 'wp-graphql' ),
				'desc'              => sprintf(
					// translators: %1$s is the site url, %2$s is the default endpoint
					__( 'The endpoint (path) for the GraphQL API on the site. <a target="_blank" href="%1$s/%2$s">%1$s/%2$s</a>. <br/><strong>Note:</strong> Changing the endpoint to something other than "graphql" <em>could</em> have an affect on tooling in the GraphQL ecosystem', 'wp-graphql' ),
					site_url(),
					get_graphql_setting( 'graphql_endpoint', 'graphql' )
				),
				'type'              => 'text',
				'value'             => ! empty( $custom_endpoint ) ? $custom_endpoint : null,
				'default'           => ! empty( $custom_endpoint ) ? $custom_endpoint : 'graphql',
				'disabled'          => ! empty( $custom_endpoint ) ? true : false,
				'sanitize_callback' => function ( $value ) {
					if ( empty( $value ) ) {
						add_settings_error( 'graphql_endpoint', 'required', __( 'The "GraphQL Endpoint" field is required and cannot be blank. The default endpoint is "graphql"', 'wp-graphql' ), 'error' );

						return 'graphql';
					}

					return $value;
				},
			]
		);

		$this->settings_api->register_fields( 'graphql_general_settings', [
			[
				'name'    => 'restrict_endpoint_to_logged_in_users',
				'label'   => __( 'Restrict Endpoint to Authenticated Users', 'wp-graphql' ),
				'desc'    => __( 'Limit the execution of GraphQL operations to authenticated requests. Non-authenticated requests to the GraphQL endpoint will not execute and will return an error.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'    => 'batch_queries_enabled',
				'label'   => __( 'Enable Batch Queries', 'wp-graphql' ),
				'desc'    => __( 'WPGraphQL supports batch queries, or the ability to send multiple GraphQL operations in a single HTTP request. Batch requests are enabled by default.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'on',
			],
			[
				'name'    => 'batch_limit',
				'label'   => __( 'Batch Query Limit', 'wp-graphql' ),
				'desc'    => __( 'If Batch Queries are enabled, this value sets the max number of batch operations to allow per request. Requests containing more batch operations than allowed will be rejected before execution.', 'wp-graphql' ),
				'type'    => 'number',
				'default' => 10,
			],
			[
				'name'    => 'query_depth_enabled',
				'label'   => __( 'Enable Query Depth Limiting', 'wp-graphql' ),
				'desc'    => __( 'Enabling this will limit the depth of queries WPGraphQL will execute using the value of the Max Depth setting.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'              => 'query_depth_max',
				'label'             => __( 'Max Depth to allow for GraphQL Queries', 'wp-graphql' ),
				'desc'              => __( 'If Query Depth limiting is enabled, this is the number of levels WPGraphQL will allow. Queries with deeper nesting will be rejected. Must be a positive integer value. Default 10.', 'wp-graphql' ),
				'type'              => 'number',
				'default'           => 10,
				'sanitize_callback' => static function ( $value ) {
					// if the entered value is not a positive integer, default to 10
					if ( ! absint( $value ) ) {
						$value = 10;
					}
					return absint( $value );
				},
			],
			[
				'name'    => 'graphiql_enabled',
				'label'   => __( 'Enable GraphiQL IDE', 'wp-graphql' ),
				'desc'    => __( 'GraphiQL IDE is a tool for exploring the GraphQL Schema and test GraphQL operations. Uncheck this to disable GraphiQL in the Dashboard.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'on',
			],
			[
				'name'    => 'show_graphiql_link_in_admin_bar',
				'label'   => __( 'GraphiQL IDE Admin Bar Link', 'wp-graphql' ),
				'desc'    => __( 'Show GraphiQL IDE Link in the WordPress Admin Bar', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'on',
			],
			[
				'name'    => 'delete_data_on_deactivate',
				'label'   => __( 'Delete Data on Deactivation', 'wp-graphql' ),
				'desc'    => __( 'Delete settings and any other data stored by WPGraphQL upon de-activation of the plugin. Un-checking this will keep data after the plugin is de-activated.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'on',
			],
			[
				'name'     => 'debug_mode_enabled',
				'label'    => __( 'Enable GraphQL Debug Mode', 'wp-graphql' ),
				'desc'     => defined( 'GRAPHQL_DEBUG' )
					// translators: %s is the value of the GRAPHQL_DEBUG constant
					? sprintf( __( 'This setting is disabled. "GRAPHQL_DEBUG" has been set to "%s" with code', 'wp-graphql' ), GRAPHQL_DEBUG ? 'true' : 'false' )
					: __( 'Whether GraphQL requests should execute in "debug" mode. This setting is disabled if <strong>GRAPHQL_DEBUG</strong> is defined in wp-config.php. <br/>This will provide more information in GraphQL errors but can leak server implementation details so this setting is <strong>NOT RECOMMENDED FOR PRODUCTION ENVIRONMENTS</strong>.', 'wp-graphql' ),
				'type'     => 'checkbox',
				'value'    => true === \WPGraphQL::debug() ? 'on' : get_graphql_setting( 'debug_mode_enabled', 'off' ),
				'disabled' => defined( 'GRAPHQL_DEBUG' ) ? true : false,
			],
			[
				'name'    => 'tracing_enabled',
				'label'   => __( 'Enable GraphQL Tracing', 'wp-graphql' ),
				'desc'    => __( 'Adds trace data to the extensions portion of GraphQL responses. This can help identify bottlenecks for specific fields.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'    => 'tracing_user_role',
				'label'   => __( 'Tracing Role', 'wp-graphql' ),
				'desc'    => __( 'If Tracing is enabled, this limits it to requests from users with the specified User Role.', 'wp-graphql' ),
				'type'    => 'user_role_select',
				'default' => 'administrator',
			],
			[
				'name'    => 'query_logs_enabled',
				'label'   => __( 'Enable GraphQL Query Logs', 'wp-graphql' ),
				'desc'    => __( 'Adds SQL Query logs to the extensions portion of GraphQL responses. <br/><strong>Note:</strong> This is a debug tool that can have an impact on performance and is not recommended to have active in production.', 'wp-graphql' ),
				'type'    => 'checkbox',
				'default' => 'off',
			],
			[
				'name'    => 'query_log_user_role',
				'label'   => __( 'Query Log Role', 'wp-graphql' ),
				'desc'    => __( 'If Query Logs are enabled, this limits them to requests from users with the specified User Role.', 'wp-graphql' ),
				'type'    => 'user_role_select',
				'default' => 'administrator',
			],
			[
				'name'     => 'public_introspection_enabled',
				'label'    => __( 'Enable Public Introspection', 'wp-graphql' ),
				'desc'     => sprintf(
					// translators: %s is either empty or a string with a note about debug mode.
					__( 'GraphQL Introspection is a feature that allows the GraphQL Schema to be queried. For Production and Staging environments, WPGraphQL will by default limit introspection queries to authenticated requests. Checking this enables Introspection for public requests, regardless of environment. %s ', 'wp-graphql' ),
					true === \WPGraphQL::debug() ? '<strong>' . __( 'NOTE: This setting is force enabled because GraphQL Debug Mode is enabled. ', 'wp-graphql' ) . '</strong>' : ''
				),
				'type'     => 'checkbox',
				'default'  => ( 'local' === $this->get_wp_environment() || 'development' === $this->get_wp_environment() ) ? 'on' : 'off',
				'value'    => true === \WPGraphQL::debug() ? 'on' : get_graphql_setting( 'public_introspection_enabled', 'off' ),
				'disabled' => true === \WPGraphQL::debug(),
			],
		] );

		// Action to hook into to register settings
		do_action( 'graphql_register_settings', $this );

	}

	/**
	 * Initialize the settings admin page
	 *
	 * @return void
	 */
	public function initialize_settings_page() {
		$this->settings_api->admin_init();
	}

	/**
	 * Initialize the styles and scripts used on the settings admin page
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function initialize_settings_page_scripts( string $hook_suffix ) : void {
		$this->settings_api->admin_enqueue_scripts( $hook_suffix );
	}

	/**
	 * Render the settings page in the admin
	 *
	 * @return void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<?php
			settings_errors();
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
			?>
		</div>
		<?php
	}

}
