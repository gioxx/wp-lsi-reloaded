<?php
/*
Plugin Name: Lightweight Social Icons Reloaded
Plugin URI: https://github.com/gioxx/wp-lsi-reloaded
Description: Add simple icon font social media buttons. Choose the order, colors, size and more for a wide range of icons. Independent fork of Lightweight Social Icons.
Version: 1.0.0
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

define( 'LSIR_VERSION', '1.0.0' );

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

		$this->scripts['lsir_scripts'] = false;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_footer-widgets.php', array( $this, 'print_scripts' ), 9999 );
	}

	/**
	* Front-end display of widget.
	*
	* @since 1.0.0
	*/
	public function widget( $args, $instance ) {
		$this->scripts['lsir_scripts'] = true;

		$title = apply_filters( 'widget_title', $instance['title'] );
		$options = lsir_icons();
		$defaults = lsir_option_defaults();
		$unique_id = esc_attr( $args['widget_id'] );
		$output = '';

		echo $args['before_widget'];

		echo ( ! empty( $title ) ) ? $args['before_title'] . $title . $args['after_title'] : '';

		$new_window = ( isset( $instance['new_window'] ) && '' !== $instance['new_window'] ) ? 'target="_blank"' : $defaults['new_window'];
		$font_size = ( isset( $instance['font_size'] ) && '' !== $instance['font_size'] ) ? $instance['font_size'] : $defaults['font_size'];
		$border_radius = ( isset( $instance['border_radius'] ) && '' !== $instance['border_radius'] ) ? $instance['border_radius'] : $defaults['border_radius'];
		$background = ( isset( $instance['background'] ) && '' !== $instance['background'] ) ? $instance['background'] : $defaults['background'];
		$color = ( isset( $instance['color'] ) && '' !== $instance['color'] ) ? $instance['color'] : $defaults['color'];
		$background_hover = ( isset( $instance['background_hover'] ) && '' !== $instance['background_hover'] ) ? $instance['background_hover'] : $defaults['background_hover'];
		$color_hover = ( isset( $instance['color_hover'] ) && '' !== $instance['color_hover'] ) ? $instance['color_hover'] : $defaults['color_hover'];
		$alignment = ( isset( $instance['alignment'] ) && '' !== $instance['alignment'] ) ? $instance['alignment'] : $defaults['alignment'];
		$tooltip = ( isset( $instance['tooltip'] ) && '' !== $instance['tooltip'] ) ? $instance['tooltip'] : $defaults['tooltip'];

		$count = 0;
		foreach ( $options as $option ) {

			$input = 'input' . $count++;
			$select = 'select' . $count++;

			$name = ( ! empty( $instance[$select] ) ) ? $instance[$select] : '';
			$value = ( ! empty( $instance[$input] ) ) ? $instance[$input] : '';

			// Icons removed from a previous version of the plugin (or the original
			// Lightweight Social Icons) may still be referenced by old stored data.
			if ( ! empty( $value ) && ! empty( $name ) && isset( $options[$name] ) ) {
				if ( is_email( $value ) ) {
					$the_value = 'mailto:' . $value;
				} elseif ( 'phone' == $name ) {
					$the_value = 'tel:' . $value;
				} elseif ( 'skype' == $name ) {
					$the_value = 'skype:' . $value;
				} else {
					$the_value = esc_url( $value );
				}

				$show_tooltip = ( ! empty( $tooltip ) ) ? 'tooltip' : '';
				$rel_attribute = apply_filters( 'lsir_icon_rel_attribute', 'rel="nofollow"' );
				$title_attribute = apply_filters( 'lsir_icon_title_attribute', 'title="' . esc_attr( $options[$name]['name'] ) . '"' );
				$accessibility = apply_filters( 'lsir_icon_aria_attribute', 'aria-label="' . esc_attr( $options[$name]['name'] ) . '"' );

				if ( $new_window && 'email' !== $name ) {
					$rel_attribute = apply_filters( 'lsir_icon_rel_attribute', 'rel="nofollow noopener noreferrer"' );
				}

				$output .= sprintf(
					'<li class="lsi-social-%3$s"><a class="%4$s" %5$s %6$s %7$s href="%1$s" %2$s><i class="lsicon lsicon-%3$s"></i></a></li>',
					$the_value,
					'email' == $name ? '' : $new_window,
					$name,
					$show_tooltip,
					$rel_attribute,
					$title_attribute,
					$accessibility
				);

			}
		}

		if ( $output ) {
			printf(
				'<ul class="lsi-social-icons icon-set-%1$s" style="text-align: %3$s">%2$s</ul>',
				$unique_id,
				apply_filters( 'lsir_icon_output', $output ),
				esc_attr( $alignment )
			);
		}

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
		wp_add_inline_style( 'lsir-style', $css, 99 );

		if ( ! empty( $tooltip ) ) {
			wp_enqueue_script( 'lsir-tooltipster', plugin_dir_url( __FILE__ ) . 'js/jquery.tooltipster.min.js', array( 'jquery' ), LSIR_VERSION, true );
		}

		echo $args['after_widget'];
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

		$c = 0;
		foreach ( $options as $option ) {
			$defaults['input' . $c++] = '';
			$defaults['select' . $c++] = '';
		}

		$instance = wp_parse_args( (array) $instance, $defaults );

		$id = $this->id;

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
				<option value="left" <?php selected( $instance['alignment'], 'left' ); ?>><?php esc_html_e( 'Left', 'lightweight-social-icons-reloaded' ); ?></option>
				<option value="center" <?php selected( $instance['alignment'], 'center' ); ?>><?php esc_html_e( 'Center', 'lightweight-social-icons-reloaded' ); ?></option>
				<option value="right" <?php selected( $instance['alignment'], 'right' ); ?>><?php esc_html_e( 'Right', 'lightweight-social-icons-reloaded' ); ?></option>
			</select>
			<?php esc_html_e( 'Alignment', 'lightweight-social-icons-reloaded' ); ?>
		</p>

		<hr />

		<ul class="social-icon-fields" style="margin-left: 0;">
			<?php
			$count = 0;
			foreach ( $options as $option ) {

				$input = 'input' . $count++;
				$select = 'select' . $count++;
				?>
				<li class="lsi-container" style="display: flex;">
					<select class="left choose-icon" name="<?php echo esc_attr( $this->get_field_name( $select ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( $select ) ); ?>">
						<option value=""></option>
						<?php foreach ( $options as $option ) { ?>
							<option value="<?php echo esc_attr( $option['id'] ); ?>" <?php selected( $instance[$select], $option['id'] ); ?>><?php echo esc_html( $option['name'] ); ?></option>
						<?php } ?>
					</select>
					<input class="widefat right social-item" id="<?php echo esc_attr( $this->get_field_id( $input ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $input ) ); ?>" type="text" value="<?php echo esc_attr( $instance[$input] ); ?>">

				</li>
			<?php } ?>

			<button onclick="event.preventDefault();lsirAddIcon(this)" class="button add-lsi-row <?php echo esc_attr( $id ); ?>" data-id="<?php echo esc_attr( $id ); ?>" style="margin-bottom:10px;"><?php esc_html_e( 'Add Icon', 'lightweight-social-icons-reloaded' ); ?></button>
		</ul>

		<script>
		jQuery(document).ready(function ($) {
			$( '.social-item' ).each( function( index ) {
				if( ! $(this).val() ) {
					$( this ).parent().hide();
				}
			});

			$('.lsi-container .choose-icon').each(function(){
				$(this).change(function() {
					var select = $(this);

					if ( $(this).attr('value') == 'phone' ) {
						$(this).next('input').attr('placeholder', '<?php esc_html_e( '1 (123)-456-7890', 'lightweight-social-icons-reloaded' ); ?>');
					} else if ( $(this).attr('value') == 'email' ) {
						$(this).next().attr('placeholder', '<?php esc_html_e( 'you@yourdomain.com or http://', 'lightweight-social-icons-reloaded' ); ?>');
					} else if ( $(this).attr('value') == 'skype' ) {
						$(this).next().attr('placeholder', '<?php esc_html_e( 'Username', 'lightweight-social-icons-reloaded' ); ?>');
					}else if ( $(this).attr('value') == '' ) {
						$(this).next().attr('placeholder','');
					} else {
						$(this).next().attr('placeholder','http://');
					}
				});
			});
		});

		function lsirAddIcon(elem) {
			jQuery( elem ).siblings('li:hidden:first').css( 'display', 'flex' );
		}
		</script>
		<?php
	}

	/**
	* Save and sanitize values
	* @since 1.0.0
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$options = lsir_icons();

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['border_radius'] = intval( $new_instance['border_radius'] );
		$instance['font_size'] = intval( $new_instance['font_size'] );
		$instance['background'] = lsir_sanitize_hex_color( $new_instance['background'] );
		$instance['color'] = lsir_sanitize_hex_color( $new_instance['color'] );
		$instance['background_hover'] = lsir_sanitize_hex_color( $new_instance['background_hover'] );
		$instance['color_hover'] = lsir_sanitize_hex_color( $new_instance['color_hover'] );
		$instance['new_window'] = ( isset( $instance['new_window'] ) ) ? strip_tags( $new_instance['new_window'] ) : '';
		$instance['alignment'] = strip_tags( $new_instance['alignment'] );
		$instance['tooltip'] = ( isset( $new_instance['tooltip'] ) ) ? strip_tags( $new_instance['tooltip'] ) : '';
		$count = 0;

		foreach ( $options as $option ) {

			$input = 'input' . $count++;
			$select = 'select' . $count++;

			$instance[$select] = strip_tags( $new_instance[$select] );

			if ( 'skype' == $new_instance[$select] ) {
				$instance[$input] = strip_tags( $new_instance[$input] );
			} elseif ( 'phone' == $new_instance[$select] ) {
				$instance[$input] = strip_tags( $new_instance[$input] );
			} elseif ( 'email' == $new_instance[$select] ) {
				if ( is_email( $new_instance[$input] ) ) {
					$instance[$input] = sanitize_email( $new_instance[$input] );
				} else {
					$instance[$input] = esc_url( $new_instance[$input] );
				}
			} else {
				$instance[$input] = esc_url( $new_instance[$input] );
			}

		}

		return $instance;
	}

	/**
	* Enqueue the admin scripts
	* @since 1.0.0
	*/
	function enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( 'widgets' !== $screen->base ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'underscore' );
	}

	public function print_scripts() {
		?>
		<script>
		( function( $ ){
			function initColorPicker( widget ) {
				widget.find( '.color-picker' ).wpColorPicker( {
					change: _.throttle( function() { // For Customizer
						$(this).trigger( 'change' );
					}, 3000 )
				});
			}

			function onFormUpdate( event, widget ) {
				initColorPicker( widget );
			}

			$( document ).on( 'widget-added widget-updated', onFormUpdate );

			$( document ).ready( function() {
				$( '#widgets-right .widget:has(.color-picker)' ).each( function () {
					initColorPicker( $( this ) );
				} );
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
* Set the default widget icons
* @since 1.0.0
*/
function lsir_icons( $options = '' ) {
	$options = array (
		'fivehundredpx' => array(
			'id' => 'fivehundredpx',
			'name' => __( '500px', 'lightweight-social-icons-reloaded' )
		),
		'angellist' => array(
			'id' => 'angellist',
			'name' => __( 'AngelList', 'lightweight-social-icons-reloaded' )
		),
		'bandcamp' => array(
			'id' => 'bandcamp',
			'name' => __( 'Bandcamp', 'lightweight-social-icons-reloaded' )
		),
		'behance' => array(
			'id' => 'behance',
			'name' => __( 'Behance', 'lightweight-social-icons-reloaded' )
		),
		'bitbucket' => array(
			'id' => 'bitbucket',
			'name' => __( 'BitBucket', 'lightweight-social-icons-reloaded' )
		),
		'bloglovin' => array(
			'id' => 'bloglovin',
			'name' => __( "Blog Lovin'", 'lightweight-social-icons-reloaded' )
		),
		'codepen' => array(
			'id' => 'codepen',
			'name' => __( 'Codepen', 'lightweight-social-icons-reloaded' )
		),
		'email' => array(
			'id' => 'email',
			'name' => __( 'Contact', 'lightweight-social-icons-reloaded' )
		),
		'deviantart' => array(
			'id' => 'deviantart',
			'name' => __( 'DeviantArt', 'lightweight-social-icons-reloaded' )
		),
		'discord' => array(
			'id' => 'discord',
			'name' => __( 'Discord', 'lightweight-social-icons-reloaded' )
		),
		'digg' => array(
			'id' => 'digg',
			'name' => __( 'Digg', 'lightweight-social-icons-reloaded' )
		),
		'dribbble' => array(
			'id' => 'dribbble',
			'name' => __( 'Dribbble', 'lightweight-social-icons-reloaded' )
		),
		'dropbox' => array(
			'id' => 'dropbox',
			'name' => __( 'Dropbox', 'lightweight-social-icons-reloaded' )
		),
		'facebook' => array(
			'id' => 'facebook',
			'name' => __( 'Facebook', 'lightweight-social-icons-reloaded' )
		),
		'flickr' => array(
			'id' => 'flickr',
			'name' => __( 'Flickr', 'lightweight-social-icons-reloaded' )
		),
		'foursquare' => array(
			'id' => 'foursquare',
			'name' => __( 'Foursquare', 'lightweight-social-icons-reloaded' )
		),
		'github' => array(
			'id' => 'github',
			'name' => __( 'Github', 'lightweight-social-icons-reloaded' )
		),
		'houzz' => array(
			'id' => 'houzz',
			'name' => __( 'Houzz', 'lightweight-social-icons-reloaded' )
		),
		'instagram' => array(
			'id' => 'instagram',
			'name' => __( 'Instagram', 'lightweight-social-icons-reloaded' )
		),
		'itunes' => array(
			'id' => 'itunes',
			'name' => __( 'iTunes', 'lightweight-social-icons-reloaded' )
		),
		'jsfiddle' => array(
			'id' => 'jsfiddle',
			'name' => __( 'JSFiddle', 'lightweight-social-icons-reloaded' )
		),
		'lastfm' => array(
			'id' => 'lastfm',
			'name' => __( 'Last.fm', 'lightweight-social-icons-reloaded' )
		),
		'linkedin' => array(
			'id' => 'linkedin',
			'name' => __( 'LinkedIn', 'lightweight-social-icons-reloaded' )
		),
		'mastodon' => array(
			'id' => 'mastodon',
			'name' => __( 'Mastodon', 'lightweight-social-icons-reloaded' )
		),
		'x' => array(
			'id' => 'x',
			'name' => __( 'X', 'lightweight-social-icons-reloaded' )
		),
		'mixcloud' => array(
			'id' => 'mixcloud',
			'name' => __( 'Mixcloud', 'lightweight-social-icons-reloaded' )
		),
		'paper-plane' => array(
			'id' => 'paper-plane',
			'name' => __( "Newsletter", 'lightweight-social-icons-reloaded' )
		),
		'phone' => array(
			'id' => 'phone',
			'name' => __( 'Phone', 'lightweight-social-icons-reloaded' )
		),
		'pinterest' => array(
			'id' => 'pinterest',
			'name' => __( 'Pinterest', 'lightweight-social-icons-reloaded' )
		),
		'reddit' => array(
			'id' => 'reddit',
			'name' => __( 'Reddit', 'lightweight-social-icons-reloaded' )
		),
		'rss' => array(
			'id' => 'rss',
			'name' => __( 'RSS', 'lightweight-social-icons-reloaded' )
		),
		'skype' => array(
			'id' => 'skype',
			'name' => __( 'Skype', 'lightweight-social-icons-reloaded' )
		),
		'snapchat' => array(
			'id' => 'snapchat',
			'name' => __( 'Snapchat', 'lightweight-social-icons-reloaded' )
		),
		'soundcloud' => array(
			'id' => 'soundcloud',
			'name' => __( 'Soundcloud', 'lightweight-social-icons-reloaded' )
		),
		'spotify' => array(
			'id' => 'spotify',
			'name' => __( 'Spotify', 'lightweight-social-icons-reloaded' )
		),
		'stackoverflow' => array(
			'id' => 'stackoverflow',
			'name' => __( 'Stack Overflow', 'lightweight-social-icons-reloaded' )
		),
		'steam' => array(
			'id' => 'steam',
			'name' => __( 'Steam', 'lightweight-social-icons-reloaded' )
		),
		'telegram' => array(
			'id' => 'telegram',
			'name' => __( 'Telegram', 'lightweight-social-icons-reloaded' )
		),
		'threads' => array(
			'id' => 'threads',
			'name' => __( 'Threads', 'lightweight-social-icons-reloaded' )
		),
		'tiktok' => array(
			'id' => 'tiktok',
			'name' => __( 'TikTok', 'lightweight-social-icons-reloaded' )
		),
		'tripadvisor' => array(
			'id' => 'tripadvisor',
			'name' => __( 'Trip Advisor', 'lightweight-social-icons-reloaded' )
		),
		'tumblr' => array(
			'id' => 'tumblr',
			'name' => __( 'Tumblr', 'lightweight-social-icons-reloaded' )
		),
		'twitch' => array(
			'id' => 'twitch',
			'name' => __( 'Twitch', 'lightweight-social-icons-reloaded' )
		),
		'twitter' => array(
			'id' => 'twitter',
			'name' => __( 'Twitter', 'lightweight-social-icons-reloaded' )
		),
		'vimeo' => array(
			'id' => 'vimeo',
			'name' => __( 'Vimeo', 'lightweight-social-icons-reloaded' )
		),
		'bluesky' => array(
			'id' => 'bluesky',
			'name' => __( 'Bluesky', 'lightweight-social-icons-reloaded' )
		),
		'whatsapp' => array(
			'id' => 'whatsapp',
			'name' => __( 'WhatsApp', 'lightweight-social-icons-reloaded' )
		),
		'vkontakte' => array(
			'id' => 'vkontakte',
			'name' => __( "VK", 'lightweight-social-icons-reloaded' )
		),
		'wordpress' => array(
			'id' => 'wordpress',
			'name' => __( 'WordPress', 'lightweight-social-icons-reloaded' )
		),
		'xing' => array(
			'id' => 'xing',
			'name' => __( 'Xing', 'lightweight-social-icons-reloaded' )
		),
		'yelp' => array(
			'id' => 'yelp',
			'name' => __( 'Yelp', 'lightweight-social-icons-reloaded' )
		),
		'youtube' => array(
			'id' => 'youtube',
			'name' => __( 'YouTube', 'lightweight-social-icons-reloaded' )
		)
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
