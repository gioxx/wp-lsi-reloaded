<?php
/*
Plugin Name: Reloaded Social Icons
Plugin URI: https://github.com/gioxx/wp-lsi-reloaded
Description: Add simple icon font social media buttons. Choose the order, colors, size and more for a wide range of icons. Independent fork of Lightweight Social Icons.
Version: 1.1.1
Requires at least: 6.0
Requires PHP: 7.4
Author: Gioxx
Author URI: https://gioxx.org
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: reloaded-social-icons

GitHub Plugin URI: gioxx/wp-lsi-reloaded
GitHub Branch: main
Release Asset: true
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LSIR_VERSION', '1.1.1' );

add_action( 'admin_notices', 'lsir_original_plugin_notice' );
/**
* Warn if the original Lightweight Social Icons plugin is still active alongside this fork,
* since both register a widget under the same id_base ('lsi_Widget') for seamless data
* inheritance, and having both active causes double registration.
*
* @since 1.0.0
*/
function lsir_original_plugin_notice() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'lightweight-social-icons/lightweight-social-icons.php' ) ) {
		echo '<div class="notice notice-warning"><p>' . wp_kses(
			__( '<strong>Reloaded Social Icons</strong> detected that the original <strong>Lightweight Social Icons</strong> plugin is still active. Please deactivate it to avoid conflicts: your widget settings will carry over automatically.', 'reloaded-social-icons' ),
			array( 'strong' => array() )
		) . '</p></div>';
	}
}

class LSIR_Widget extends WP_Widget {

	/**
	* Register the widget.
	*
	* Keeps the original 'lsi_Widget' id_base on purpose: WordPress stores widget
	* settings under the option `widget_{id_base}` and sidebar placements reference
	* the same id_base, so anyone migrating from the original plugin inherits their
	* widget instances, settings and sidebar position automatically.
	*
	* @since 1.0.0
	*/
	function __construct() {
		parent::__construct(
			'lsi_Widget',
			esc_html__( 'Reloaded Social Icons', 'reloaded-social-icons' )
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_scripts' ) );
		add_action( 'admin_footer-widgets.php', array( $this, 'print_scripts' ), 9999 );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_scripts' ), 9999 );
	}

	/**
	* Front-end display of widget.
	*
	* @since 1.0.0
	*/
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		$options = lsir_icons();
		$defaults = lsir_option_defaults();
		$unique_id = sanitize_html_class( $args['widget_id'] );
		$output = '';

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		$new_window = ( isset( $instance['new_window'] ) && '' !== $instance['new_window'] ) ? 'target="_blank"' : $defaults['new_window'];
		$font_size = ( isset( $instance['font_size'] ) && '' !== $instance['font_size'] ) ? $instance['font_size'] : $defaults['font_size'];
		$border_radius = ( isset( $instance['border_radius'] ) && '' !== $instance['border_radius'] ) ? $instance['border_radius'] : $defaults['border_radius'];
		$background = ( isset( $instance['background'] ) && '' !== $instance['background'] ) ? $instance['background'] : $defaults['background'];
		$color = ( isset( $instance['color'] ) && '' !== $instance['color'] ) ? $instance['color'] : $defaults['color'];
		$background_hover = ( isset( $instance['background_hover'] ) && '' !== $instance['background_hover'] ) ? $instance['background_hover'] : $defaults['background_hover'];
		$color_hover = ( isset( $instance['color_hover'] ) && '' !== $instance['color_hover'] ) ? $instance['color_hover'] : $defaults['color_hover'];
		$alignment = ( isset( $instance['alignment'] ) && '' !== $instance['alignment'] ) ? $instance['alignment'] : $defaults['alignment'];
		$tooltip = ( isset( $instance['tooltip'] ) && '' !== $instance['tooltip'] ) ? $instance['tooltip'] : $defaults['tooltip'];

		foreach ( lsir_get_icon_rows( $instance ) as $row ) {
			$name = $row['icon'];
			$value = $row['value'];

			// Icons removed from the plugin may still be referenced by old stored data.
			if ( '' === $name || ! isset( $options[ $name ] ) ) {
				continue;
			}

			if ( is_email( $value ) ) {
				$the_value = esc_url( 'mailto:' . $value, array( 'mailto' ) );
			} elseif ( 'phone' === $name ) {
				$the_value = esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $value ), array( 'tel' ) );
			} else {
				$the_value = esc_url( $value );
			}

			if ( '' === $the_value ) {
				continue;
			}

			$show_tooltip = ( ! empty( $tooltip ) ) ? 'tooltip' : '';
			$rel_attribute = apply_filters( 'lsir_icon_rel_attribute', 'rel="nofollow"' );
			$title_attribute = apply_filters( 'lsir_icon_title_attribute', 'title="' . esc_attr( $options[ $name ]['name'] ) . '"' );
			$accessibility = apply_filters( 'lsir_icon_aria_attribute', 'aria-label="' . esc_attr( $options[ $name ]['name'] ) . '"' );

			if ( $new_window && 'email' !== $name ) {
				$rel_attribute = apply_filters( 'lsir_icon_rel_attribute', 'rel="nofollow noopener noreferrer"' );
			}

			$output .= sprintf(
				'<li class="lsi-social-%3$s"><a class="%4$s" %5$s %6$s %7$s href="%1$s" %2$s><i class="lsicon lsicon-%3$s"></i></a></li>',
				$the_value,
				'email' === $name ? '' : $new_window,
				esc_attr( $name ),
				$show_tooltip,
				$rel_attribute,
				$title_attribute,
				$accessibility
			);
		}

		if ( $output ) {
			printf(
				'<ul class="lsi-social-icons icon-set-%1$s" style="text-align: %3$s">%2$s</ul>',
				esc_attr( $unique_id ),
				wp_kses( apply_filters( 'lsir_icon_output', $output ), lsir_icon_allowed_html() ),
				esc_attr( $alignment )
			);
		}

		// Values are sanitized on save; re-check here since they end up in a stylesheet.
		$font_size = absint( $font_size );
		$border_radius = absint( $border_radius );
		$background = lsir_sanitize_hex_color( $background ) ? $background : $defaults['background'];
		$color = lsir_sanitize_hex_color( $color ) ? $color : $defaults['color'];
		$background_hover = lsir_sanitize_hex_color( $background_hover ) ? $background_hover : $defaults['background_hover'];
		$color_hover = lsir_sanitize_hex_color( $color_hover ) ? $color_hover : $defaults['color_hover'];

		$css = ".icon-set-{$unique_id} a,
		.icon-set-{$unique_id} a:visited,
		.icon-set-{$unique_id} a:focus {
			border-radius: {$border_radius}px;
			background: {$background} !important;
			color: {$color} !important;
			font-size: {$font_size}px !important;
		}

		.icon-set-{$unique_id} a:hover {
			background: {$background_hover} !important;
			color: {$color_hover} !important;
		}";

		wp_enqueue_style( 'lsir-style', plugin_dir_url( __FILE__ ) . 'css/style-min.css', array(), LSIR_VERSION, 'all' );
		wp_add_inline_style( 'lsir-style', $css );

		if ( ! empty( $tooltip ) ) {
			wp_enqueue_script( 'lsir-tooltipster', plugin_dir_url( __FILE__ ) . 'js/jquery.tooltipster.min.js', array( 'jquery' ), LSIR_VERSION, true );
		}

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	* Build the actual widget in the Dashboard
	* @since 1.0.0
	*/
	public function form( $instance ) {

		$options = lsir_icons();

		$defaults = lsir_option_defaults();

		$title = ( isset( $instance[ 'title' ] ) && '' !== $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		$border_radius = ( isset( $instance[ 'border_radius' ] ) && '' !== $instance[ 'border_radius' ] ) ? $instance[ 'border_radius' ] : $defaults['border_radius'];
		$font_size = ( isset( $instance[ 'font_size' ] ) && '' !== $instance[ 'font_size' ] ) ? $instance[ 'font_size' ] : $defaults['font_size'];
		$background = ( isset( $instance[ 'background' ] ) && '' !== $instance[ 'background' ] ) ? $instance[ 'background' ] : $defaults['background'];
		$color = ( isset( $instance[ 'color' ] ) && '' !== $instance[ 'color' ] ) ? $instance[ 'color' ] : $defaults['color'];
		$background_hover = ( isset( $instance[ 'background_hover' ] ) && '' !== $instance[ 'background_hover' ] ) ? $instance[ 'background_hover' ] : $defaults['background_hover'];
		$color_hover = ( isset( $instance[ 'color_hover' ] ) && '' !== $instance[ 'color_hover' ] ) ? $instance[ 'color_hover' ] : $defaults['color_hover'];
		$alignment = ( isset( $instance[ 'alignment' ] ) && '' !== $instance[ 'alignment' ] ) ? $instance[ 'alignment' ] : $defaults['alignment'];
		$tooltip = ( isset( $instance[ 'tooltip' ] ) && '' !== $instance[ 'tooltip' ] ) ? $instance[ 'tooltip' ] : $defaults['tooltip'];
		$new_window = ( isset( $instance[ 'new_window' ] ) && '' !== $instance[ 'new_window' ] ) ? $instance[ 'new_window' ] : $defaults['new_window'];

		// Saved icons first (in their saved order), then enough empty rows to add
		// every available icon once.
		$rows = array_values( array_filter( lsir_get_icon_rows( $instance ), function ( $row ) use ( $options ) {
			// Hide rows for icons no longer offered by the plugin (e.g. Skype).
			return '' === $row['icon'] || isset( $options[ $row['icon'] ] );
		} ) );
		$total = max( count( $options ), count( $rows ) );
		for ( $i = count( $rows ); $i < $total; $i++ ) {
			$rows[] = array( 'icon' => '', 'value' => '' );
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'reloaded-social-icons' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo intval( $font_size ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Icon Size', 'reloaded-social-icons' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" type="text" value="<?php echo intval( $border_radius ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Border Radius', 'reloaded-social-icons' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'background' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background' ) ); ?>" type="text" value="<?php echo esc_attr( $background ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Color', 'reloaded-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>" type="text" value="<?php echo esc_attr( $color ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Color', 'reloaded-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'background_hover' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_hover' ) ); ?>" type="text" value="<?php echo esc_attr( $background_hover ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Hover Color', 'reloaded-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'color_hover' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color_hover' ) ); ?>" type="text" value="<?php echo esc_attr( $color_hover ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Hover Color', 'reloaded-social-icons' ); ?></label>
		</p>

		<hr />

		<p>
			<label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'new_window' ) ); ?>" value="1" <?php checked( 1, $new_window ); ?>/>
				<?php esc_html_e( 'Open links in new window?', 'reloaded-social-icons' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'tooltip' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'tooltip' ) ); ?>" value="1" <?php checked( 1, $tooltip ); ?>/>
				<?php esc_html_e( 'Enable tooltips?', 'reloaded-social-icons' ); ?>
			</label>
		</p>

		<p>
			<select name="<?php echo esc_attr( $this->get_field_name( 'alignment' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'alignment' ) ); ?>">
				<option value="left" <?php selected( $alignment, 'left' ); ?>><?php esc_html_e( 'Left', 'reloaded-social-icons' ); ?></option>
				<option value="center" <?php selected( $alignment, 'center' ); ?>><?php esc_html_e( 'Center', 'reloaded-social-icons' ); ?></option>
				<option value="right" <?php selected( $alignment, 'right' ); ?>><?php esc_html_e( 'Right', 'reloaded-social-icons' ); ?></option>
			</select>
			<?php esc_html_e( 'Alignment', 'reloaded-social-icons' ); ?>
		</p>

		<hr />

		<ul class="lsir-icons social-icon-fields">
			<?php
			foreach ( $rows as $index => $row ) {
				$input = 'input' . ( 2 * $index );
				$select = 'select' . ( 2 * $index + 1 );
				$visible = '' !== $row['value'];
				?>
				<li class="lsi-container"<?php echo $visible ? '' : ' style="display:none;"'; ?>>
					<span class="lsir-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'reloaded-social-icons' ); ?>"></span>
					<select class="choose-icon" name="<?php echo esc_attr( $this->get_field_name( $select ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( $select ) ); ?>">
						<option value=""></option>
						<?php foreach ( $options as $option ) { ?>
							<option value="<?php echo esc_attr( $option['id'] ); ?>" <?php selected( $row['icon'], $option['id'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
						<?php } ?>
					</select>
					<input class="widefat social-item" id="<?php echo esc_attr( $this->get_field_id( $input ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $input ) ); ?>" type="text" value="<?php echo esc_attr( $row['value'] ); ?>">
					<button type="button" class="button-link lsir-remove" aria-label="<?php esc_attr_e( 'Remove icon', 'reloaded-social-icons' ); ?>" title="<?php esc_attr_e( 'Remove icon', 'reloaded-social-icons' ); ?>"><span class="dashicons dashicons-no-alt"></span></button>
				</li>
			<?php } ?>
		</ul>

		<p>
			<button type="button" class="button add-lsi-row"><?php esc_html_e( 'Add Icon', 'reloaded-social-icons' ); ?></button>
		</p>
		<p class="description" style="margin-top:12px;font-size:11px;">
			<?php
			printf(
				/* translators: 1: plugin name, 2: link to the author's site, 3: link to the author's plugin gallery */
				esc_html__( '%1$s by %2$s · %3$s', 'reloaded-social-icons' ),
				'Reloaded Social Icons',
				'<a href="https://gioxx.org" target="_blank" rel="noopener noreferrer">Gioxx</a>',
				'<a href="https://wordpress.gxware.org/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'More plugins', 'reloaded-social-icons' ) . '</a>'
			);
			?>
		</p>
		<?php
	}

	/**
	* Save and sanitize values.
	*
	* Icon rows are compacted on save: rows without a URL are dropped and the
	* remaining ones are renumbered in order, so removed icons leave no gaps.
	*
	* @since 1.0.0
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$options = lsir_icons();

		$alignment = isset( $new_instance['alignment'] ) ? $new_instance['alignment'] : '';

		$instance['title'] = sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' );
		$instance['border_radius'] = intval( isset( $new_instance['border_radius'] ) ? $new_instance['border_radius'] : 0 );
		$instance['font_size'] = intval( isset( $new_instance['font_size'] ) ? $new_instance['font_size'] : 0 );
		$instance['background'] = lsir_sanitize_hex_color( isset( $new_instance['background'] ) ? $new_instance['background'] : '' );
		$instance['color'] = lsir_sanitize_hex_color( isset( $new_instance['color'] ) ? $new_instance['color'] : '' );
		$instance['background_hover'] = lsir_sanitize_hex_color( isset( $new_instance['background_hover'] ) ? $new_instance['background_hover'] : '' );
		$instance['color_hover'] = lsir_sanitize_hex_color( isset( $new_instance['color_hover'] ) ? $new_instance['color_hover'] : '' );
		$instance['new_window'] = ! empty( $new_instance['new_window'] ) ? '1' : '';
		$instance['alignment'] = in_array( $alignment, array( 'left', 'center', 'right' ), true ) ? $alignment : 'left';
		$instance['tooltip'] = ! empty( $new_instance['tooltip'] ) ? '1' : '';

		// Drop every stored icon slot, then write the compacted list back.
		foreach ( array_keys( (array) $instance ) as $key ) {
			if ( preg_match( '/^(select|input)\d+$/', $key ) ) {
				unset( $instance[ $key ] );
			}
		}

		$index = 0;
		foreach ( lsir_get_icon_rows( $new_instance ) as $row ) {
			$icon = $row['icon'];

			// Icons no longer offered by the plugin (e.g. Skype) are dropped.
			if ( '' !== $icon && ! isset( $options[ $icon ] ) ) {
				continue;
			}

			$value = lsir_sanitize_icon_value( $icon, $row['value'] );

			if ( '' === $value ) {
				continue;
			}

			$instance[ 'input' . ( 2 * $index ) ] = $value;
			$instance[ 'select' . ( 2 * $index + 1 ) ] = $icon;
			$index++;
		}

		return $instance;
	}

	/**
	* Enqueue the admin scripts on the Widgets screen.
	* @since 1.0.0
	*/
	function enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( ! $screen || 'widgets' !== $screen->base ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	* Enqueue the admin scripts in the Customizer, which can also edit widgets.
	* @since 1.1.0
	*/
	function enqueue_customizer_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	* Admin-side behaviour of the widget form: color pickers, placeholders,
	* removing, adding and reordering icon rows. Printed once per page and bound
	* with delegated events, so it also works on widgets added or saved via AJAX.
	* @since 1.0.0
	*/
	public function print_scripts() {
		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;

		$placeholders = array(
			'phone' => __( '1 (123)-456-7890', 'reloaded-social-icons' ),
			'email' => __( 'you@yourdomain.com or https://', 'reloaded-social-icons' ),
			'url'   => 'https://',
		);
		?>
		<style>
			.lsir-icons { margin: 0 0 4px; }
			.lsir-icons li.lsi-container { display: flex; align-items: center; gap: 4px; margin-bottom: 6px; }
			.lsir-icons .lsir-handle { cursor: move; color: #8c8f94; flex: 0 0 auto; }
			.lsir-icons .choose-icon { flex: 0 0 auto; max-width: 40%; }
			.lsir-icons .social-item { flex: 1 1 auto; min-width: 0; }
			.lsir-icons .lsir-remove { flex: 0 0 auto; color: #b32d2e; text-decoration: none; cursor: pointer; }
			.lsir-icons .lsir-remove:hover, .lsir-icons .lsir-remove:focus { color: #8a2424; }
			.lsir-icons .ui-sortable-placeholder { visibility: visible !important; border: 1px dashed #c3c4c7; height: 30px; }
		</style>
		<script>
		( function( $ ) {
			var placeholders = <?php echo wp_json_encode( $placeholders ); ?>;

			function initColorPicker( widget ) {
				widget.find( '.color-picker' ).wpColorPicker( {
					change: _.throttle( function() { // For Customizer
						$( this ).trigger( 'change' );
					}, 3000 )
				} );
			}

			function updatePlaceholder( $select ) {
				var icon = $select.val();
				var text = '';

				if ( 'phone' === icon ) {
					text = placeholders.phone;
				} else if ( 'email' === icon ) {
					text = placeholders.email;
				} else if ( icon ) {
					text = placeholders.url;
				}

				$select.siblings( '.social-item' ).attr( 'placeholder', text );
			}

			// Keep field names in DOM order, so the saved order matches what the user sees.
			function renumber( $list ) {
				$list.children( 'li' ).each( function( i ) {
					$( this ).find( '.choose-icon' ).each( function() {
						this.name = this.name.replace( /\[select\d+\]$/, '[select' + ( 2 * i + 1 ) + ']' );
						this.id = this.id.replace( /select\d+$/, 'select' + ( 2 * i + 1 ) );
					} );
					$( this ).find( '.social-item' ).each( function() {
						this.name = this.name.replace( /\[input\d+\]$/, '[input' + ( 2 * i ) + ']' );
						this.id = this.id.replace( /input\d+$/, 'input' + ( 2 * i ) );
					} );
				} );
			}

			// Tell WordPress (Widgets screen and Customizer) that the form changed.
			function markChanged( $list ) {
				$list.find( '.social-item' ).first().trigger( 'change' );
			}

			function initIconList( $scope ) {
				$scope.find( '.lsir-icons' ).each( function() {
					var $list = $( this );

					$list.find( '.choose-icon' ).each( function() {
						updatePlaceholder( $( this ) );
					} );

					if ( $list.data( 'lsirSortable' ) || ! $.fn.sortable ) {
						return;
					}
					$list.data( 'lsirSortable', true );

					$list.sortable( {
						items: '> li',
						handle: '.lsir-handle',
						axis: 'y',
						update: function() {
							renumber( $list );
							markChanged( $list );
						}
					} );
				} );
			}

			$( document ).on( 'change', '.lsir-icons .choose-icon', function() {
				updatePlaceholder( $( this ) );
			} );

			$( document ).on( 'click', '.lsir-icons .lsir-remove', function( event ) {
				var $row = $( this ).closest( 'li' );
				var $list = $row.parent();

				event.preventDefault();

				$row.find( '.choose-icon' ).val( '' );
				$row.find( '.social-item' ).val( '' ).attr( 'placeholder', '' );
				$row.hide().appendTo( $list );

				renumber( $list );
				markChanged( $list );
			} );

			$( document ).on( 'click', '.add-lsi-row', function( event ) {
				event.preventDefault();
				$( this ).closest( 'p' ).prev( '.lsir-icons' ).children( 'li:hidden:first' ).css( 'display', 'flex' ).find( '.choose-icon' ).trigger( 'focus' );
			} );

			$( document ).on( 'widget-added widget-updated', function( event, widget ) {
				initColorPicker( widget );
				initIconList( widget );
			} );

			$( function() {
				$( '#widgets-right .widget:has(.color-picker)' ).each( function() {
					initColorPicker( $( this ) );
				} );
				initIconList( $( document.body ) );
			} );
		}( jQuery ) );
		</script>
		<?php
	}

}

add_action( 'widgets_init', 'lsir_register_widget' );
/**
* Register the widget
* @since 1.0.0
*/
function lsir_register_widget() {
	register_widget( 'LSIR_Widget' );
}

/**
* Set the widget option defaults
* @since 1.0.0
*/
function lsir_option_defaults() {
	$defaults = array(
		'title' => '',
		'new_window' => '',
		'border_radius' => 2,
		'font_size' => 20,
		'background' => '#1E72BD',
		'color' => '#FFFFFF',
		'background_hover' => '#777777',
		'color_hover' => '#FFFFFF',
		'alignment' => 'left',
		'tooltip' => ''
	);

	return apply_filters( 'lsir_option_defaults', $defaults );
}

/**
* Old icon ids that now render as a different icon.
*
* Stored widget data keeps working: these ids are shown with the new icon and
* are saved under the new id the next time the widget is saved.
*
* @since 1.1.0
*/
function lsir_icon_aliases() {
	$aliases = array(
		'twitter'    => 'x',
		'foursquare' => 'swarm',
		'itunes'     => 'applemusic',
		'angellist'  => 'wellfound',
	);

	return apply_filters( 'lsir_icon_aliases', $aliases );
}

/**
* Read the icon rows stored in a widget instance, in order.
*
* Widget data stores each row as a pair of keys: `input{2n}` (the URL) and
* `select{2n+1}` (the icon id). The number of rows is read from the data itself,
* so instances saved with a different icon count (older plugin versions, the
* original Lightweight Social Icons) are read in full. Rows without a URL are
* skipped and legacy icon ids are mapped through lsir_icon_aliases().
*
* @since 1.1.0
*
* @param array $instance Widget instance (stored or submitted).
* @return array List of array( 'icon' => string, 'value' => string ).
*/
function lsir_get_icon_rows( $instance ) {
	$aliases = lsir_icon_aliases();
	$rows = array();

	foreach ( (array) $instance as $key => $icon ) {
		if ( ! preg_match( '/^select(\d+)$/', $key, $match ) ) {
			continue;
		}

		$slot = (int) $match[1];
		$input_key = 'input' . ( $slot - 1 );
		$value = isset( $instance[ $input_key ] ) ? trim( (string) $instance[ $input_key ] ) : '';

		if ( '' === $value ) {
			continue;
		}

		$icon = (string) $icon;
		if ( isset( $aliases[ $icon ] ) ) {
			$icon = $aliases[ $icon ];
		}

		$rows[ $slot ] = array(
			'icon'  => $icon,
			'value' => $value,
		);
	}

	ksort( $rows, SORT_NUMERIC );

	return array_values( $rows );
}

/**
* HTML allowed in the icon list markup (also after the lsir_icon_output filter).
*
* @since 1.1.0
*/
function lsir_icon_allowed_html() {
	$allowed = array(
		'li' => array( 'class' => true ),
		'a'  => array(
			'class'      => true,
			'href'       => true,
			'rel'        => true,
			'title'      => true,
			'target'     => true,
			'aria-label' => true,
		),
		'i'  => array( 'class' => true, 'aria-hidden' => true ),
	);

	return apply_filters( 'lsir_icon_allowed_html', $allowed );
}

/**
* Sanitize the value (URL, email or phone number) of one icon row.
*
* @since 1.1.0
*
* @param string $icon  Icon id.
* @param string $value Raw value.
* @return string Sanitized value, empty if invalid.
*/
function lsir_sanitize_icon_value( $icon, $value ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );

	if ( 'phone' === $icon ) {
		return sanitize_text_field( $value );
	}

	if ( 'email' === $icon && is_email( $value ) ) {
		return sanitize_email( $value );
	}

	return esc_url_raw( $value );
}

/**
* Set the default widget icons
* @since 1.0.0
*/
function lsir_icons( $options = '' ) {
	$options = array(
		'fivehundredpx' => array( 'id' => 'fivehundredpx', 'name' => __( '500px', 'reloaded-social-icons' ) ),
		'amazon'        => array( 'id' => 'amazon', 'name' => __( 'Amazon', 'reloaded-social-icons' ) ),
		'applemusic'    => array( 'id' => 'applemusic', 'name' => __( 'Apple Music', 'reloaded-social-icons' ) ),
		'applepodcasts' => array( 'id' => 'applepodcasts', 'name' => __( 'Apple Podcasts', 'reloaded-social-icons' ) ),
		'bandcamp'      => array( 'id' => 'bandcamp', 'name' => __( 'Bandcamp', 'reloaded-social-icons' ) ),
		'behance'       => array( 'id' => 'behance', 'name' => __( 'Behance', 'reloaded-social-icons' ) ),
		'bitbucket'     => array( 'id' => 'bitbucket', 'name' => __( 'BitBucket', 'reloaded-social-icons' ) ),
		'bluesky'       => array( 'id' => 'bluesky', 'name' => __( 'Bluesky', 'reloaded-social-icons' ) ),
		'buymeacoffee'  => array( 'id' => 'buymeacoffee', 'name' => __( 'Buy Me a Coffee', 'reloaded-social-icons' ) ),
		'codeberg'      => array( 'id' => 'codeberg', 'name' => __( 'Codeberg', 'reloaded-social-icons' ) ),
		'codepen'       => array( 'id' => 'codepen', 'name' => __( 'Codepen', 'reloaded-social-icons' ) ),
		'email'         => array( 'id' => 'email', 'name' => __( 'Contact', 'reloaded-social-icons' ) ),
		'deviantart'    => array( 'id' => 'deviantart', 'name' => __( 'DeviantArt', 'reloaded-social-icons' ) ),
		'discord'       => array( 'id' => 'discord', 'name' => __( 'Discord', 'reloaded-social-icons' ) ),
		'dribbble'      => array( 'id' => 'dribbble', 'name' => __( 'Dribbble', 'reloaded-social-icons' ) ),
		'dropbox'       => array( 'id' => 'dropbox', 'name' => __( 'Dropbox', 'reloaded-social-icons' ) ),
		'facebook'      => array( 'id' => 'facebook', 'name' => __( 'Facebook', 'reloaded-social-icons' ) ),
		'flickr'        => array( 'id' => 'flickr', 'name' => __( 'Flickr', 'reloaded-social-icons' ) ),
		'github'        => array( 'id' => 'github', 'name' => __( 'GitHub', 'reloaded-social-icons' ) ),
		'gitlab'        => array( 'id' => 'gitlab', 'name' => __( 'GitLab', 'reloaded-social-icons' ) ),
		'goodreads'     => array( 'id' => 'goodreads', 'name' => __( 'Goodreads', 'reloaded-social-icons' ) ),
		'houzz'         => array( 'id' => 'houzz', 'name' => __( 'Houzz', 'reloaded-social-icons' ) ),
		'instagram'     => array( 'id' => 'instagram', 'name' => __( 'Instagram', 'reloaded-social-icons' ) ),
		'jsfiddle'      => array( 'id' => 'jsfiddle', 'name' => __( 'JSFiddle', 'reloaded-social-icons' ) ),
		'kick'          => array( 'id' => 'kick', 'name' => __( 'Kick', 'reloaded-social-icons' ) ),
		'kofi'          => array( 'id' => 'kofi', 'name' => __( 'Ko-fi', 'reloaded-social-icons' ) ),
		'lastfm'        => array( 'id' => 'lastfm', 'name' => __( 'Last.fm', 'reloaded-social-icons' ) ),
		'lemmy'         => array( 'id' => 'lemmy', 'name' => __( 'Lemmy', 'reloaded-social-icons' ) ),
		'letterboxd'    => array( 'id' => 'letterboxd', 'name' => __( 'Letterboxd', 'reloaded-social-icons' ) ),
		'linkedin'      => array( 'id' => 'linkedin', 'name' => __( 'LinkedIn', 'reloaded-social-icons' ) ),
		'mastodon'      => array( 'id' => 'mastodon', 'name' => __( 'Mastodon', 'reloaded-social-icons' ) ),
		'medium'        => array( 'id' => 'medium', 'name' => __( 'Medium', 'reloaded-social-icons' ) ),
		'mixcloud'      => array( 'id' => 'mixcloud', 'name' => __( 'Mixcloud', 'reloaded-social-icons' ) ),
		'paper-plane'   => array( 'id' => 'paper-plane', 'name' => __( 'Newsletter', 'reloaded-social-icons' ) ),
		'patreon'       => array( 'id' => 'patreon', 'name' => __( 'Patreon', 'reloaded-social-icons' ) ),
		'peertube'      => array( 'id' => 'peertube', 'name' => __( 'PeerTube', 'reloaded-social-icons' ) ),
		'phone'         => array( 'id' => 'phone', 'name' => __( 'Phone', 'reloaded-social-icons' ) ),
		'pinterest'     => array( 'id' => 'pinterest', 'name' => __( 'Pinterest', 'reloaded-social-icons' ) ),
		'pixelfed'      => array( 'id' => 'pixelfed', 'name' => __( 'Pixelfed', 'reloaded-social-icons' ) ),
		'reddit'        => array( 'id' => 'reddit', 'name' => __( 'Reddit', 'reloaded-social-icons' ) ),
		'rss'           => array( 'id' => 'rss', 'name' => __( 'RSS', 'reloaded-social-icons' ) ),
		'signal'        => array( 'id' => 'signal', 'name' => __( 'Signal', 'reloaded-social-icons' ) ),
		'snapchat'      => array( 'id' => 'snapchat', 'name' => __( 'Snapchat', 'reloaded-social-icons' ) ),
		'soundcloud'    => array( 'id' => 'soundcloud', 'name' => __( 'SoundCloud', 'reloaded-social-icons' ) ),
		'spotify'       => array( 'id' => 'spotify', 'name' => __( 'Spotify', 'reloaded-social-icons' ) ),
		'stackoverflow' => array( 'id' => 'stackoverflow', 'name' => __( 'Stack Overflow', 'reloaded-social-icons' ) ),
		'steam'         => array( 'id' => 'steam', 'name' => __( 'Steam', 'reloaded-social-icons' ) ),
		'strava'        => array( 'id' => 'strava', 'name' => __( 'Strava', 'reloaded-social-icons' ) ),
		'substack'      => array( 'id' => 'substack', 'name' => __( 'Substack', 'reloaded-social-icons' ) ),
		'swarm'         => array( 'id' => 'swarm', 'name' => __( 'Swarm', 'reloaded-social-icons' ) ),
		'telegram'      => array( 'id' => 'telegram', 'name' => __( 'Telegram', 'reloaded-social-icons' ) ),
		'threads'       => array( 'id' => 'threads', 'name' => __( 'Threads', 'reloaded-social-icons' ) ),
		'tiktok'        => array( 'id' => 'tiktok', 'name' => __( 'TikTok', 'reloaded-social-icons' ) ),
		'tripadvisor'   => array( 'id' => 'tripadvisor', 'name' => __( 'Tripadvisor', 'reloaded-social-icons' ) ),
		'tumblr'        => array( 'id' => 'tumblr', 'name' => __( 'Tumblr', 'reloaded-social-icons' ) ),
		'twitch'        => array( 'id' => 'twitch', 'name' => __( 'Twitch', 'reloaded-social-icons' ) ),
		'vimeo'         => array( 'id' => 'vimeo', 'name' => __( 'Vimeo', 'reloaded-social-icons' ) ),
		'vkontakte'     => array( 'id' => 'vkontakte', 'name' => __( 'VK', 'reloaded-social-icons' ) ),
		'wellfound'     => array( 'id' => 'wellfound', 'name' => __( 'Wellfound (AngelList)', 'reloaded-social-icons' ) ),
		'whatsapp'      => array( 'id' => 'whatsapp', 'name' => __( 'WhatsApp', 'reloaded-social-icons' ) ),
		'wordpress'     => array( 'id' => 'wordpress', 'name' => __( 'WordPress', 'reloaded-social-icons' ) ),
		'x'             => array( 'id' => 'x', 'name' => __( 'X (Twitter)', 'reloaded-social-icons' ) ),
		'xing'          => array( 'id' => 'xing', 'name' => __( 'Xing', 'reloaded-social-icons' ) ),
		'yelp'          => array( 'id' => 'yelp', 'name' => __( 'Yelp', 'reloaded-social-icons' ) ),
		'youtube'       => array( 'id' => 'youtube', 'name' => __( 'YouTube', 'reloaded-social-icons' ) ),
	);

	return apply_filters( 'lsir_icons_defaults', $options );
}

/**
* Function to sanitize the hex values
* @since 1.0.0
*/
function lsir_sanitize_hex_color( $color ) {
	if ( '' === $color ) {
		return '';
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}

	return null;
}

/**
 * Links under the plugin description on the Plugins screen: the GitHub
 * repository (unless the row already links to it) and the author's gallery.
 *
 * @param string[] $links Row meta links.
 * @param string   $file  Plugin basename of the row.
 * @return string[]
 */
function lsir_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) !== $file ) {
		return $links;
	}
	if ( false === strpos( implode( ' ', (array) $links ), 'https://github.com/gioxx/wp-lsi-reloaded' ) ) {
		$links[] = '<a href="https://github.com/gioxx/wp-lsi-reloaded" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View on GitHub', 'reloaded-social-icons' ) . '</a>';
	}
	$links[] = '<a href="https://wordpress.gxware.org/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'More plugins by Gioxx', 'reloaded-social-icons' ) . '</a>';

	return $links;
}
add_filter( 'plugin_row_meta', 'lsir_plugin_row_meta', 10, 2 );
