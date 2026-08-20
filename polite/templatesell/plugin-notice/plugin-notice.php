<?php
/**
 * Recommended plugins notice.
 *
 * Everything listed here is hosted on WordPress.org and entirely optional - the
 * theme is fully functional without any of it. Nothing is bundled and nothing is
 * installed on the user's behalf; the links below hand off to core's own plugin
 * installer, behind a capability check and a nonce.
 *
 * @package Polite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'polite_recommended_plugins' ) ) :
	/**
	 * The plugins worth suggesting, in the order they are shown.
	 *
	 * @return array[] Each entry has a slug, the main plugin file and a display name.
	 */
	function polite_recommended_plugins() {

		return array(
			array(
				'slug' => 'one-click-demo-import',
				'file' => 'one-click-demo-import/one-click-demo-import.php',
				'name' => __( 'One Click Demo Import', 'polite' ),
			),
			array(
				'slug' => 'template-sell-demo-importer',
				'file' => 'template-sell-demo-importer/templatesell-demo-importer.php',
				'name' => __( 'Template Sell Demo Importer', 'polite' ),
			),
			array(
				'slug' => 'templatesell-gated-downloads',
				'file' => 'templatesell-gated-downloads/templatesell-gated-downloads.php',
				'name' => __( 'Gated Downloads', 'polite' ),
			),
			array(
				'slug' => 'templatesell-captchaflow',
				'file' => 'templatesell-captchaflow/captchaflow.php',
				'name' => __( 'CaptchaFlow', 'polite' ),
			),
		);
	}
endif;

if ( ! function_exists( 'polite_missing_recommended_plugins' ) ) :
	/**
	 * List the recommended plugins that are not active yet.
	 *
	 * @return array[] Subset of polite_recommended_plugins(), each with an added
	 *                 'installed' flag so the notice can offer the right action.
	 */
	function polite_missing_recommended_plugins() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed = array_keys( get_plugins() );
		$missing   = array();

		foreach ( polite_recommended_plugins() as $plugin ) {
			if ( is_plugin_active( $plugin['file'] ) ) {
				continue;
			}

			$plugin['installed'] = in_array( $plugin['file'], $installed, true );
			$missing[]           = $plugin;
		}

		return $missing;
	}
endif;

if ( ! function_exists( 'polite_plugin_action_url' ) ) :
	/**
	 * Build the install or activate URL for one recommended plugin.
	 *
	 * Both are core screens, nonced the way core nonces them.
	 *
	 * @param array $plugin One entry from polite_recommended_plugins().
	 * @return string Admin URL, or an empty string if the user cannot act on it.
	 */
	function polite_plugin_action_url( $plugin ) {

		if ( $plugin['installed'] ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return '';
			}

			return wp_nonce_url(
				self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin['file'] ) ),
				'activate-plugin_' . $plugin['file']
			);
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return '';
		}

		return wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=' . rawurlencode( $plugin['slug'] ) ),
			'install-plugin_' . $plugin['slug']
		);
	}
endif;

if ( ! function_exists( 'polite_plugin_notice' ) ) :
	/**
	 * Show the recommendation until it is dismissed or everything is active.
	 */
	function polite_plugin_notice() {

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), 'polite_plugin_notice_dismissed', true ) ) {
			return;
		}

		$missing = polite_missing_recommended_plugins();

		if ( empty( $missing ) ) {
			return;
		}

		// Use the active theme's own name, so child themes read correctly too.
		$theme_name = wp_get_theme()->get( 'Name' );

		$names = array();
		foreach ( $missing as $plugin ) {
			$names[] = '<em>' . esc_html( $plugin['name'] ) . '</em>';
		}
		?>
		<div class="notice notice-info is-dismissible polite-plugin-notice">
			<p>
				<?php
				printf(
					/* translators: 1: theme name, 2: comma separated list of plugin names. */
					esc_html__( '%1$s recommends these free plugins: %2$s.', 'polite' ),
					esc_html( $theme_name ),
					wp_kses( implode( ', ', $names ), array( 'em' => array() ) )
				);
				?>
				<?php esc_html_e( 'None of them are required.', 'polite' ); ?>
			</p>
			<p>
				<?php
				foreach ( $missing as $plugin ) {
					$url = polite_plugin_action_url( $plugin );

					if ( '' === $url ) {
						continue;
					}

					printf(
						'<a href="%1$s" class="button button-secondary">%2$s</a> ',
						esc_url( $url ),
						$plugin['installed']
							/* translators: %s: plugin name. */
							? esc_html( sprintf( __( 'Activate %s', 'polite' ), $plugin['name'] ) )
							/* translators: %s: plugin name. */
							: esc_html( sprintf( __( 'Install %s', 'polite' ), $plugin['name'] ) )
					);
				}
				?>
			</p>
			<p>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=polite-theme' ) ); ?>"><?php esc_html_e( 'Theme options', 'polite' ); ?></a>
			<span aria-hidden="true">|</span>				<a href="<?php echo esc_url( polite_plugin_notice_dismiss_url() ); ?>"><?php esc_html_e( 'Dismiss this notice', 'polite' ); ?></a>
			</p>
		</div>
		<?php
	}
endif;
add_action( 'admin_notices', 'polite_plugin_notice' );

if ( ! function_exists( 'polite_plugin_notice_dismiss_url' ) ) :
	/**
	 * Nonced URL that dismisses the notice for the current user.
	 *
	 * @return string
	 */
	function polite_plugin_notice_dismiss_url() {

		return wp_nonce_url(
			add_query_arg( 'polite_dismiss_plugins', '1' ),
			'polite_dismiss_plugins',
			'polite_plugins_nonce'
		);
	}
endif;

if ( ! function_exists( 'polite_plugin_notice_dismiss' ) ) :
	/**
	 * Store the dismissal against the user.
	 */
	function polite_plugin_notice_dismiss() {

		if ( ! isset( $_GET['polite_dismiss_plugins'] ) ) {
			return;
		}

		if ( ! isset( $_GET['polite_plugins_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['polite_plugins_nonce'] ) ), 'polite_dismiss_plugins' )
		) {
			return;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), 'polite_plugin_notice_dismissed', 1 );
	}
endif;
add_action( 'admin_init', 'polite_plugin_notice_dismiss' );
