<?php
/*
Plugin Name: Lightweight Social Icons Reloaded
Plugin URI: https://github.com/gioxx/wp-lsi-reloaded
Description: Add simple icon font social media buttons. Choose the order, colors, size and more for a wide range of icons. Independent fork of Lightweight Social Icons.
Version: 1.1.0
Requires at least: 6.0
Requires PHP: 7.4
Author: Gioxx
Author URI: https://gioxx.org
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lightweight-social-icons-reloaded

GitHub Plugin URI: gioxx/wp-lsi-reloaded
GitHub Branch: main
Release Asset: true
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LSIR_VERSION', '1.1.0' );

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
			__( '<strong>Lightweight Social Icons Reloaded</strong> detected that the original <strong>Lightweight Social Icons</strong> plugin is still active. Please deactivate it to avoid conflicts: your widget settings will carry over automatically.', 'lightweight-social-icons-reloaded' ),
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
			esc_html__( 'Lightweight Social Icons Reloaded', 'lightweight-social-icons-reloaded' )
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'lightweight-social-icons-reloaded' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo intval( $font_size ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Icon Size', 'lightweight-social-icons-reloaded' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo esc_attr( $this->get_field_id( 'border_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'border_radius' ) ); ?>" type="text" value="<?php echo intval( $border_radius ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Border Radius', 'lightweight-social-icons-reloaded' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'background' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background' ) ); ?>" type="text" value="<?php echo esc_attr( $background ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Color', 'lightweight-social-icons-reloaded' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>" type="text" value="<?php echo esc_attr( $color ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Color', 'lightweight-social-icons-reloaded' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'background_hover' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_hover' ) ); ?>" type="text" value="<?php echo esc_attr( $background_hover ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Hover Color', 'lightweight-social-icons-reloaded' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo esc_attr( $this->get_field_id( 'color_hover' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color_hover' ) ); ?>" type="text" value="<?php echo esc_attr( $color_hover ); ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Hover Color', 'lightweight-social-icons-reloaded' ); ?></label>
		</p>

		<hr />

		<p>
			<label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'new_window' ) ); ?>" value="1" <?php checked( 1, $new_window ); ?>/>
				<?php esc_html_e( 'Open links in new window?', 'lightweight-social-icons-reloaded' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'tooltip' ) ); ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'tooltip' ) ); ?>" value="1" <?php checked( 1, $tooltip ); ?>/>
				<?php esc_html_e( 'Enable tooltips?', 'lightweight-social-icons-reloaded' ); ?>
			</label>
		</p>

		<p>
			<select name="<?php echo esc_attr( $this->get_field_name( 'alignment' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'alignment' ) ); ?>">
				<option value="left" <?php selected( $alignment, 'left' ); ?>><?php esc_html_e( 'Left', 'lightweight-social-icons-reloaded' ); ?></option>
				<option value="center" <?php selected( $alignment, 'center' ); ?>><?php esc_html_e( 'Center', 'lightweight-social-icons-reloaded' ); ?></option>
				<option value="right" <?php selected( $alignment, 'right' ); ?>><?php esc_html_e( 'Right', 'lightweight-social-icons-reloaded' ); ?></option>
			</select>
			<?php esc_html_e( 'Alignment', 'lightweight-social-icons-reloaded' ); ?>
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
					<span class="lsir-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'lightweight-social-icons-reloaded' ); ?>"></span>
					<select class="choose-icon" name="<?php echo esc_attr( $this->get_field_name( $select ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( $select ) ); ?>">
						<option value=""></option>
						<?php foreach ( $options as $option ) { ?>
							<option value="<?php echo esc_attr( $option['id'] ); ?>" <?php selected( $row['icon'], $option['id'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
						<?php } ?>
					</select>
					<input class="widefat social-item" id="<?php echo esc_attr( $this->get_field_id( $input ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $input ) ); ?>" type="text" value="<?php echo esc_attr( $row['value'] ); ?>">
					<button type="button" class="button-link lsir-remove" aria-label="<?php esc_attr_e( 'Remove icon', 'lightweight-social-icons-reloaded' ); ?>" title="<?php esc_attr_e( 'Remove icon', 'lightweight-social-icons-reloaded' ); ?>"><span class="dashicons dashicons-no-alt"></span></button>
				</li>
			<?php } ?>
		</ul>

		<p>
			<button type="button" class="button add-lsi-row"><?php esc_html_e( 'Add Icon', 'lightweight-social-icons-reloaded' ); ?></button>
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
			'phone' => __( '1 (123)-456-7890', 'lightweight-social-icons-reloaded' ),
			'email' => __( 'you@yourdomain.com or https://', 'lightweight-social-icons-reloaded' ),
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
		'fivehundredpx' => array( 'id' => 'fivehundredpx', 'name' => __( '500px', 'lightweight-social-icons-reloaded' ) ),
		'amazon'        => array( 'id' => 'amazon', 'name' => __( 'Amazon', 'lightweight-social-icons-reloaded' ) ),
		'applemusic'    => array( 'id' => 'applemusic', 'name' => __( 'Apple Music', 'lightweight-social-icons-reloaded' ) ),
		'applepodcasts' => array( 'id' => 'applepodcasts', 'name' => __( 'Apple Podcasts', 'lightweight-social-icons-reloaded' ) ),
		'bandcamp'      => array( 'id' => 'bandcamp', 'name' => __( 'Bandcamp', 'lightweight-social-icons-reloaded' ) ),
		'behance'       => array( 'id' => 'behance', 'name' => __( 'Behance', 'lightweight-social-icons-reloaded' ) ),
		'bitbucket'     => array( 'id' => 'bitbucket', 'name' => __( 'BitBucket', 'lightweight-social-icons-reloaded' ) ),
		'bluesky'       => array( 'id' => 'bluesky', 'name' => __( 'Bluesky', 'lightweight-social-icons-reloaded' ) ),
		'buymeacoffee'  => array( 'id' => 'buymeacoffee', 'name' => __( 'Buy Me a Coffee', 'lightweight-social-icons-reloaded' ) ),
		'codeberg'      => array( 'id' => 'codeberg', 'name' => __( 'Codeberg', 'lightweight-social-icons-reloaded' ) ),
		'codepen'       => array( 'id' => 'codepen', 'name' => __( 'Codepen', 'lightweight-social-icons-reloaded' ) ),
		'email'         => array( 'id' => 'email', 'name' => __( 'Contact', 'lightweight-social-icons-reloaded' ) ),
		'deviantart'    => array( 'id' => 'deviantart', 'name' => __( 'DeviantArt', 'lightweight-social-icons-reloaded' ) ),
		'discord'       => array( 'id' => 'discord', 'name' => __( 'Discord', 'lightweight-social-icons-reloaded' ) ),
		'dribbble'      => array( 'id' => 'dribbble', 'name' => __( 'Dribbble', 'lightweight-social-icons-reloaded' ) ),
		'dropbox'       => array( 'id' => 'dropbox', 'name' => __( 'Dropbox', 'lightweight-social-icons-reloaded' ) ),
		'facebook'      => array( 'id' => 'facebook', 'name' => __( 'Facebook', 'lightweight-social-icons-reloaded' ) ),
		'flickr'        => array( 'id' => 'flickr', 'name' => __( 'Flickr', 'lightweight-social-icons-reloaded' ) ),
		'github'        => array( 'id' => 'github', 'name' => __( 'GitHub', 'lightweight-social-icons-reloaded' ) ),
		'gitlab'        => array( 'id' => 'gitlab', 'name' => __( 'GitLab', 'lightweight-social-icons-reloaded' ) ),
		'goodreads'     => array( 'id' => 'goodreads', 'name' => __( 'Goodreads', 'lightweight-social-icons-reloaded' ) ),
		'houzz'         => array( 'id' => 'houzz', 'name' => __( 'Houzz', 'lightweight-social-icons-reloaded' ) ),
		'instagram'     => array( 'id' => 'instagram', 'name' => __( 'Instagram', 'lightweight-social-icons-reloaded' ) ),
		'jsfiddle'      => array( 'id' => 'jsfiddle', 'name' => __( 'JSFiddle', 'lightweight-social-icons-reloaded' ) ),
		'kick'          => array( 'id' => 'kick', 'name' => __( 'Kick', 'lightweight-social-icons-reloaded' ) ),
		'kofi'          => array( 'id' => 'kofi', 'name' => __( 'Ko-fi', 'lightweight-social-icons-reloaded' ) ),
		'lastfm'        => array( 'id' => 'lastfm', 'name' => __( 'Last.fm', 'lightweight-social-icons-reloaded' ) ),
		'lemmy'         => array( 'id' => 'lemmy', 'name' => __( 'Lemmy', 'lightweight-social-icons-reloaded' ) ),
		'letterboxd'    => array( 'id' => 'letterboxd', 'name' => __( 'Letterboxd', 'lightweight-social-icons-reloaded' ) ),
		'linkedin'      => array( 'id' => 'linkedin', 'name' => __( 'LinkedIn', 'lightweight-social-icons-reloaded' ) ),
		'mastodon'      => array( 'id' => 'mastodon', 'name' => __( 'Mastodon', 'lightweight-social-icons-reloaded' ) ),
		'medium'        => array( 'id' => 'medium', 'name' => __( 'Medium', 'lightweight-social-icons-reloaded' ) ),
		'mixcloud'      => array( 'id' => 'mixcloud', 'name' => __( 'Mixcloud', 'lightweight-social-icons-reloaded' ) ),
		'paper-plane'   => array( 'id' => 'paper-plane', 'name' => __( 'Newsletter', 'lightweight-social-icons-reloaded' ) ),
		'patreon'       => array( 'id' => 'patreon', 'name' => __( 'Patreon', 'lightweight-social-icons-reloaded' ) ),
		'peertube'      => array( 'id' => 'peertube', 'name' => __( 'PeerTube', 'lightweight-social-icons-reloaded' ) ),
		'phone'         => array( 'id' => 'phone', 'name' => __( 'Phone', 'lightweight-social-icons-reloaded' ) ),
		'pinterest'     => array( 'id' => 'pinterest', 'name' => __( 'Pinterest', 'lightweight-social-icons-reloaded' ) ),
		'pixelfed'      => array( 'id' => 'pixelfed', 'name' => __( 'Pixelfed', 'lightweight-social-icons-reloaded' ) ),
		'reddit'        => array( 'id' => 'reddit', 'name' => __( 'Reddit', 'lightweight-social-icons-reloaded' ) ),
		'rss'           => array( 'id' => 'rss', 'name' => __( 'RSS', 'lightweight-social-icons-reloaded' ) ),
		'signal'        => array( 'id' => 'signal', 'name' => __( 'Signal', 'lightweight-social-icons-reloaded' ) ),
		'snapchat'      => array( 'id' => 'snapchat', 'name' => __( 'Snapchat', 'lightweight-social-icons-reloaded' ) ),
		'soundcloud'    => array( 'id' => 'soundcloud', 'name' => __( 'SoundCloud', 'lightweight-social-icons-reloaded' ) ),
		'spotify'       => array( 'id' => 'spotify', 'name' => __( 'Spotify', 'lightweight-social-icons-reloaded' ) ),
		'stackoverflow' => array( 'id' => 'stackoverflow', 'name' => __( 'Stack Overflow', 'lightweight-social-icons-reloaded' ) ),
		'steam'         => array( 'id' => 'steam', 'name' => __( 'Steam', 'lightweight-social-icons-reloaded' ) ),
		'strava'        => array( 'id' => 'strava', 'name' => __( 'Strava', 'lightweight-social-icons-reloaded' ) ),
		'substack'      => array( 'id' => 'substack', 'name' => __( 'Substack', 'lightweight-social-icons-reloaded' ) ),
		'swarm'         => array( 'id' => 'swarm', 'name' => __( 'Swarm', 'lightweight-social-icons-reloaded' ) ),
		'telegram'      => array( 'id' => 'telegram', 'name' => __( 'Telegram', 'lightweight-social-icons-reloaded' ) ),
		'threads'       => array( 'id' => 'threads', 'name' => __( 'Threads', 'lightweight-social-icons-reloaded' ) ),
		'tiktok'        => array( 'id' => 'tiktok', 'name' => __( 'TikTok', 'lightweight-social-icons-reloaded' ) ),
		'tripadvisor'   => array( 'id' => 'tripadvisor', 'name' => __( 'Tripadvisor', 'lightweight-social-icons-reloaded' ) ),
		'tumblr'        => array( 'id' => 'tumblr', 'name' => __( 'Tumblr', 'lightweight-social-icons-reloaded' ) ),
		'twitch'        => array( 'id' => 'twitch', 'name' => __( 'Twitch', 'lightweight-social-icons-reloaded' ) ),
		'vimeo'         => array( 'id' => 'vimeo', 'name' => __( 'Vimeo', 'lightweight-social-icons-reloaded' ) ),
		'vkontakte'     => array( 'id' => 'vkontakte', 'name' => __( 'VK', 'lightweight-social-icons-reloaded' ) ),
		'wellfound'     => array( 'id' => 'wellfound', 'name' => __( 'Wellfound (AngelList)', 'lightweight-social-icons-reloaded' ) ),
		'whatsapp'      => array( 'id' => 'whatsapp', 'name' => __( 'WhatsApp', 'lightweight-social-icons-reloaded' ) ),
		'wordpress'     => array( 'id' => 'wordpress', 'name' => __( 'WordPress', 'lightweight-social-icons-reloaded' ) ),
		'x'             => array( 'id' => 'x', 'name' => __( 'X (Twitter)', 'lightweight-social-icons-reloaded' ) ),
		'xing'          => array( 'id' => 'xing', 'name' => __( 'Xing', 'lightweight-social-icons-reloaded' ) ),
		'yelp'          => array( 'id' => 'yelp', 'name' => __( 'Yelp', 'lightweight-social-icons-reloaded' ) ),
		'youtube'       => array( 'id' => 'youtube', 'name' => __( 'YouTube', 'lightweight-social-icons-reloaded' ) ),
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
