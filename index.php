<?php
/**
 * Plugin Name: Weather widget for wordpress
 * Plugin URI: http://yasiradnan.com
 * Description: A widget adds weather forecast to your wordpress sidebar
 * Version: 2.0
 * Author: Yasir Adnan
 * Author URI: http://yasiradnan.com
 */
/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'WordPress_Weather_widgets' );

/**
 * Add function to wp_print_styles  that'll load our CSS.
 */

add_action('wp_print_styles', 'add_my_stylesheet');
 
/**
 * acessing the stylesheet
 */

 	function add_my_stylesheet() {
        $myStyleUrl = plugins_url('weatherstyle.css', __FILE__); // Respects SSL, Style.css is relative to the current file
        $myStyleFile = WP_PLUGIN_DIR . '/wordpress-weather-widget/weatherstyle.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('myStyleSheets', $myStyleUrl);
            wp_enqueue_style( 'myStyleSheets');
        }
    }

/**
 * Register our widget.
 */
	function WordPress_Weather_widgets() {
	register_widget( 'Weather_Widget' );
}

/**
 * Weather_Widget class.
 */
	class Weather_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Weather_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'WP_Weather', 'description' => __('A widget that displays Weather Forecast', 'example') );

		/* Create the widget. */
		$this->WP_Widget( 'Weather-widget', __('Weather Widget', 'example'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$name = $instance['name'];
		

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<ul>';
	/**
	 *Weather API
	 */

	$xml = simplexml_load_file('http://www.google.com/ig/api?weather='.$name);
	$information = $xml->xpath("/xml_api_reply/weather/forecast_information");
	$current = $xml->xpath("/xml_api_reply/weather/current_conditions");
	$forecast_list = $xml->xpath("/xml_api_reply/weather/forecast_conditions");

	/**
	 *Converting temperature F to C.
	 */

	function toCelsius($deg) {
    	return floor(($deg-32)/1.8);}

?>
<html>
	<head></head>
    <body>
        <h2>Today's weather:<?php echo $name; ?></h2>
        
        <div class="weather">       
            
            <?php $cond=$current[0]->condition['data'];
            include 'condition.php';?>
            <span class="condition">
            <?= $current[0]->temp_c['data'] ?>&deg; C,
            <?= $current[0]->condition['data'] ?>
            </span>
        </div>
        <h2><b>Forecast</b></h2>
        <? foreach ($forecast_list as $forecast) : ?>
        <div class="weather">
           	<?php $cond = $forecast[0]->condition['data'];
            include 'condition.php';?>
            <div><?= $forecast->day_of_week['data']; ?></div>
            <span class="condition">
                <?= toCelsius($forecast->low['data']) ?>&deg; C - <?= toCelsius($forecast->high['data']) ?>&deg; C,
              
            </span>
        </div>  
        <? endforeach ?>
    </body>
</html>

<?	echo '</ul>';
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['name'] = strip_tags( $new_instance['name'] );
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Weather Widget', 'name' => '', );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!--Name -->
		<p>
			<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e('City Name:', 'example'); ?></label>
			<input id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" value="<?php echo $instance['name']; ?>" style="width:100%;" />
		</p>

		
		<?php 
	}
}
?>