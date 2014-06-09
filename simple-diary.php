<?php
/*
Plugin Name: Simple Diary for WordPress
Text Domain: simdiaw
Plugin URI: http://perso.jojaba.fr/
Description: Provides a very simple way to define reminders for diary. Embedded in default themes and customisable for others, responsive admin pages, translatable.
Author: Jojaba
Version: 1.0
Author URI: http://perso.jojaba.fr/
*/


/* The language init */
function simdiaw_lang_init() {
 load_plugin_textdomain( 'simdiaw', false, basename(dirname(__FILE__)) );
}
add_action('plugins_loaded', 'simdiaw_lang_init');

/* The template functions */
require_once( dirname( __FILE__ ) . '/simdiaw-template-functions.php' );

/* ************************************************************** */
/* Custom posts init for : 'reminder'                             */
/* ************************************************************** */
// Init
 function simdiaw_reminders_custom_init() {
    $options = get_option( 'simdiaw_settings_options' );
    $simdiaw_slug = ($options['simdiaw_slug'] != '') ? $options['simdiaw_slug'] : __('diary', 'simdiaw');
    $labels = array(
      'name'               => __('Reminders','simdiaw'),
      'singular_name'      => __('Reminder','simdiaw'),
      'add_new'            => __('New','simdiaw'),
      'add_new_item'       => __('Add a reminder','simdiaw'),
      'edit_item'          => __('Modify the reminder','simdiaw'),
      'new_item'           => __('New reminder','simdiaw'),
      'all_items'          => __('All reminders','simdiaw'),
      'view_item'          => __('Show the reminder','simdiaw'),
      'search_items'       => __('Search for reminders','simdiaw'),
      'parent_item_colon'  => '',
      'menu_name'          => __('Diary','simdiaw')
    );
    
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'menu_position'      => 5,
      'menu_icon'          => 'dashicons-pressthis',
      'query_var'          => true,
      'rewrite'            => array( 'slug' => $simdiaw_slug ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'supports'           => array( 'title', 'comments' ),
      'taxonomies'         => array( 'post_tag' ),
    );
    
    register_post_type( 'reminder', $args );
}
add_action( 'init', 'simdiaw_reminders_custom_init' );

// Flushing rewrite rules on activation or desactivation
function simdiaw_activate() {
    simdiaw_reminders_custom_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'simdiaw_activate' );

function simdiaw_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'simdiaw_deactivate' );

// Add filter to ensure the text Reminder, or reminder, is displayed when user updates a reminder
function simdiaw_reminder_updated_messages( $messages ) {
  global $post, $post_ID, $post_type, $post_type_object;

  $messages['reminder'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Reminder updated. <a href="%s">View reminder</a>', 'simdiaw'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.', 'simdiaw'),
    3 => __('Custom field deleted.', 'simdiaw'),
    4 => __('Reminder updated.', 'simdiaw'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Reminder restored to revision from %s', 'simdiaw'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Reminder published. <a href="%s">View reminder</a>', 'simdiaw'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Reminder saved.', 'simdiaw'),
    8 => sprintf( __('Reminder submitted. <a target="_blank" href="%s">Preview reminder</a>', 'simdiaw'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Reminder scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview reminder</a>', 'simdiaw'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Reminder draft updated. <a target="_blank" href="%s">Preview reminder</a>', 'simdiaw'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );
  
  return $messages;
}
add_filter( 'post_updated_messages', 'simdiaw_reminder_updated_messages' );

/* ************************************************************** */
/* The edit page for diary (reminders)                            */
/* ************************************************************** */

// Defining columns in edit page
function add_simdiaw_columns($gallery_columns) {
    // Fetching options values
    $options = get_option( 'simdiaw_settings_options' );
    $columns_in_edit_page = (is_array($options['columns_in_edit_page'])) ? $options['columns_in_edit_page'] : array('end_date_column', 'location_column', 'creation_column');	
    // Creating the columns
    $new_columns['cb'] = '<input type="checkbox" />';
    $new_columns['title'] = __('Reminders','simdiaw');
    $new_columns['start_date'] = __( 'Starting date', 'simdiaw' );
    if (in_array('end_date_column', $columns_in_edit_page))
        $new_columns['end_date'] = __( 'Ending date', 'simdiaw' );
    if (in_array('start_time_column', $columns_in_edit_page))
        $new_columns['start_time'] = __( 'Starting time', 'simdiaw' );
    if (in_array('end_time_column', $columns_in_edit_page))
        $new_columns['end_time'] = __( 'Ending time', 'simdiaw' );
    if (in_array('location_column', $columns_in_edit_page))
        $new_columns['location'] = __('Location','simdiaw');
    if (in_array('creation_column', $columns_in_edit_page))
        $new_columns['date'] = __('Created or modified', 'simdiaw');
 
    return $new_columns;
}
add_filter('manage_edit-reminder_columns', 'add_simdiaw_columns');

// Column content
function manage_simdiaw_reminder_columns($column_name, $id) {
    global $wpdb;
    switch ($column_name) {
    
        case 'start_date':
            $get_start_date = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = 'simdiaw-start-date';");
            echo date_i18n( get_option( 'date_format' ), strtotime( $get_start_date ) );
        break;
        
        case 'end_date':
            $get_end_date = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = 'simdiaw-end-date';");
            if ($get_end_date != '') echo date_i18n( get_option( 'date_format' ), strtotime( $get_end_date ) );
        break;
        
        case 'start_time':
            $get_start_time = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = 'simdiaw-start-time';");
            if ($get_start_time != '') echo date_i18n( get_option( 'time_format' ), strtotime( $get_start_time ) );
        break;
        
        case 'end_time':
            $get_end_time = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = 'simdiaw-end-time';");
            if ($get_end_time != '') echo date_i18n( get_option( 'time_format' ), strtotime( $get_end_time ) );
        break;
        
        case 'location':
            $get_location = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = 'simdiaw-loc';");
            echo $get_location;
        break;
        
        default:
        break;
    }
}   
add_action('manage_reminder_posts_custom_column', 'manage_simdiaw_reminder_columns', 10, 2);

// Make column sortable
function simdiaw_reminders_sort($columns) {
	$columns['start_date'] = 'start_date';
	$columns['end_date'] = 'end_date';
    return $columns;
}
add_filter("manage_edit-reminder_sortable_columns", 'simdiaw_reminders_sort');

// Default sorting by reminders start_date
function set_simdiaw_reminders_admin_order($wp_query) {
  if (is_admin()) {

    $post_type = $wp_query->query['post_type'];

    if ( $post_type == 'reminder' && !isset($_GET['orderby'])) {
    	$wp_query->set('meta_key', 'simdiaw-start-date');
      	$wp_query->set('orderby', 'simdiaw-start-date');
      	$wp_query->set('order', 'DESC');
    }

  }
}
add_filter ( 'pre_get_posts', 'set_simdiaw_reminders_admin_order' );

/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'simdiaw_edit_reminder_load' );

function simdiaw_edit_reminder_load() {
	add_filter( 'request', 'simdiaw_sort_reminders' );
}

// Sorting possibilities : by start_date and end_date.
function simdiaw_sort_reminders( $vars ) {

	/* Check if we're viewing the 'reminder' post type. */
	if ( isset( $vars['post_type'] ) && 'reminder' == $vars['post_type'] ) {

		/* Check if 'orderby' is set to 'start_date'. */
		if ( isset( $vars['orderby'] ) && 'start_date' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'simdiaw-start-date',
					'orderby' => 'simdiaw-start-date'
				)
			);
		}
		
		/* Check if 'orderby' is set to 'end_date'. */
		if ( isset( $vars['orderby'] ) && 'end_date' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'simdiaw-end-date',
					'orderby' => 'simdiaw-end-date'
				)
			);
		}
	}

	return $vars;
}

/* ************************************************************** */
/* Printing and managing the compose window for reminders         */
/* ************************************************************** */

/**
 * Adds a meta box to the post editing screen
 */
function simdiaw_custom_meta() {
    add_meta_box( 'simdiaw_meta', __( 'Info about the reminder', 'simdiaw' ), 'simdiaw_meta_callback', 'reminder', 'advanced', 'high' );
}
add_action( 'add_meta_boxes', 'simdiaw_custom_meta' );

/**
 * Outputs the content of the meta box
 */
function simdiaw_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'simdiaw_nonce' );
    $simdiaw_stored_meta = get_post_meta( $post->ID );
    ?>
    
    <!-- The date -->
    
    <div class="dashicons dashicons-calendar" title="<?php _e( 'DATE', 'simdiaw' )?>"></div>
    
    <div class="simdiaw-start-date">
        <label for="simdiaw-start-date"><?php _e( 'Starting date', 'simdiaw' )?> <span class="req">*</span></label><br>
        <input required type="text" name="simdiaw-start-date" id="simdiaw-start-date" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-start-date'] ) ) echo $simdiaw_stored_meta['simdiaw-start-date'][0]; ?>" />
    </div>
       
    <div class="simdiaw-end-date">
        <label for="simdiaw-end-date"><?php _e( 'Ending date', 'simdiaw' )?></label><br>
        <input type="text" name="simdiaw-end-date" id="simdiaw-end-date" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-end-date'] ) ) echo $simdiaw_stored_meta['simdiaw-end-date'][0]; ?>" />
    </div>
    
    
    <!-- The time -->
    
    <div class="dashicons dashicons-clock" title="<?php _e( 'TIME', 'simdiaw' )?>"></div>
    
    <div class="simdiaw-start-time">
        <label for="simdiaw-start-date"><?php _e( 'Starting time', 'simdiaw' )?></label><br>
        <input type="text" name="simdiaw-start-time" id="simdiaw-start-time" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-start-time'] ) ) echo $simdiaw_stored_meta['simdiaw-start-time'][0]; ?>" />
    </div>
       
    <div class="simdiaw-end-time">
        <label for="simdiaw-end-date"><?php _e( 'Ending time', 'simdiaw' )?></label><br>
        <input type="text" name="simdiaw-end-time" id="simdiaw-end-time" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-end-time'] ) ) echo $simdiaw_stored_meta['simdiaw-end-time'][0]; ?>" />
    </div>
    
    
    <!-- The location (Required) -->
    <div class="dashicons dashicons-location-alt" title="<?php _e( 'LOCATION', 'simdiaw' )?>"></div>
    
    <div class="simdiaw-location simdiaw-row">
        <label for="simdiaw-loc"><?php _e( 'Location', 'simdiaw' )?> <span class="req">*</span></label><br>
        <input required type="text" name="simdiaw-loc" id="simdiaw-loc" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-loc'] ) ) echo $simdiaw_stored_meta['simdiaw-loc'][0]; ?>" />
    </div>

    <!-- The URL or the Article (optionnal) -->
    <div class="dashicons dashicons-share-alt2" title="<?php _e( 'LINK', 'simdiaw' )?>"></div>
    
    <div class="simdiaw-url">
        <label for="simdiaw-url"><?php _e( 'Either an URL', 'simdiaw' )?></label><br>
        <input type="url" placeholder="http://..." name="simdiaw-url" id="simdiaw-url" value="<?php if ( isset ( $simdiaw_stored_meta['simdiaw-url'] ) ) echo $simdiaw_stored_meta['simdiaw-url'][0]; ?>" />
    </div>
    
    <div class="simdiaw-art">
        <?php
        // The function retrieving the published posts
        if( !function_exists('get_published_posts_data_for_simdiaw'))  {
        	function get_published_posts_data_for_simdiaw($saved_pid){
        		$d_option = '<option value="">'.__( 'No article selected', 'simdiaw' ).'</option>';        		
        		/* Get the database datas */
        		global $wpdb;
        		$posts_list = $wpdb->get_results("SELECT ID, post_title FROM wp_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
        		// Echo the options
        		foreach($posts_list as $post_d) {
        			$p_title = $post_d->post_title;
        			$p_id = $post_d->ID;
        			$d_option .= ($p_id == $saved_pid) ? '<option value="'.$p_id.'" selected="selected">'.$p_title.'</option>' : '<option value="'.$p_id.'">'.$p_title.'</option>';
        		}
        		echo $d_option;
        	}
        }
        ?>
        <label for="simdiaw-art-id"><?php _e( '… or an article', 'simdiaw' )?></label><br>
        <select name="simdiaw-art-id" id="simdiaw-art-id">
            <?php get_published_posts_data_for_simdiaw($simdiaw_stored_meta['simdiaw-art-id'][0]) ?>
        </select>
    </div>
   
    <p class="req"><span>*</span> <?php _e( 'required informations.', 'simdiaw' )?></p>
    
    <script>
    jQuery(document).ready(function(){
        /* The date, time picker displaying */
        // Date picker
        jQuery("#simdiaw-start-date, #simdiaw-end-date").on("click", function() {
            jQuery(this).pickadate({
                format: 'yyyy-mm-dd',
                formatSubmit: 'yyyy-mm-dd',
                hiddenName: true
            });
        });
        // Time picker
        jQuery("#simdiaw-start-time, #simdiaw-end-time").on("click", function() {
            jQuery(this).pickatime({
                format: 'HH:i',
                formatSubmit: 'HH:i',
                hiddenName: true
            });
        });
        
        /* url and article behaviour */
        if (jQuery("#simdiaw-art-id").val() > 0)  jQuery("#simdiaw-url").css("color", "#ddd");
        jQuery("#simdiaw-art-id").on("change", function() {
            if (jQuery(this).val() > 0) jQuery("#simdiaw-url").css("color", "#ddd");
            else jQuery("#simdiaw-url").css("color", "#333");
        });
    });
    </script>

    <?php
}

/**
 * Saves the custom meta input
 */
function simdiaw_meta_save( $post_id ) {
 
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'simdiaw_nonce' ] ) && wp_verify_nonce( $_POST[ 'simdiaw_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    // Checks for start date input and sanitizes/saves if needed
    if( isset( $_POST[ 'simdiaw-start-date' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-start-date', $_POST[ 'simdiaw-start-date' ] );
    }

    // Checks for end date input and sanitizes/saves if needed
    if( isset( $_POST[ 'simdiaw-end-date' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-end-date', $_POST[ 'simdiaw-end-date' ] );
    }
    
    // Checks for start time input and sanitizes/saves if needed
    if( isset( $_POST[ 'simdiaw-start-time' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-start-time', $_POST[ 'simdiaw-start-time' ] );
    }

    // Checks for end time input and sanitizes/saves if needed
    if( isset( $_POST[ 'simdiaw-end-time' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-end-time', $_POST[ 'simdiaw-end-time' ] );
    }
   
    // Checks for location input and sanitizes/saves if needed
    if( isset( $_POST[ 'simdiaw-loc' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-loc', sanitize_text_field( $_POST[ 'simdiaw-loc' ] ) );
    }
   
    // Checks for url input and sanitizes/saves if needed
    if( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $_POST[ 'simdiaw-url' ]) ) {
        update_post_meta( $post_id, 'simdiaw-url', $_POST[ 'simdiaw-url' ] );
    }
    
     // Checks for article select box and saves
    if( isset( $_POST[ 'simdiaw-art-id' ] ) ) {
        update_post_meta( $post_id, 'simdiaw-art-id', $_POST[ 'simdiaw-art-id' ] );
    }

}
add_action( 'save_post', 'simdiaw_meta_save' );


/**
 * Adds the meta box stylesheet when appropriate
 */
function simdiaw_admin_styles(){
    global $typenow;
    if( $typenow == 'reminder' ) {
        wp_enqueue_style( 'simdiaw_meta_box_styles', plugin_dir_url( __FILE__ ) . 'simple-diary.css' );
    }
}
add_action( 'admin_print_styles', 'simdiaw_admin_styles' );


/**
 * Loads the date & time picker js and css
 */
function simdiaw_date_time_picker_enqueue() {
    global $typenow;
    if( $typenow == 'reminder' ) {
        wp_enqueue_style( 'simdiaw_picker_style', plugin_dir_url( __FILE__ ) . 'pickadate/lib/themes/default.css' );
        wp_enqueue_style( 'simdiaw_date_style', plugin_dir_url( __FILE__ ) . 'pickadate/lib/themes/default.date.css' );
        wp_enqueue_style( 'simdiaw_time_style', plugin_dir_url( __FILE__ ) . 'pickadate/lib/themes/default.time.css' );
        wp_enqueue_script( 'simdiaw-box-picker-js', plugin_dir_url( __FILE__ ) . 'pickadate/lib/picker.js' );
        wp_enqueue_script( 'simdiaw-box-date-js', plugin_dir_url( __FILE__ ) . 'pickadate/lib/picker.date.js' );
        wp_enqueue_script( 'simdiaw-box-time-js', plugin_dir_url( __FILE__ ) . 'pickadate/lib/picker.time.js' );
        wp_enqueue_script( 'simdiaw-box-legacy-js', plugin_dir_url( __FILE__ ) . 'pickadate/lib/legacy.js' );
        if (file_exists('../wp-content/plugins/simple-diary/pickadate/lib/translations/'.WPLANG.'.js'))
            wp_enqueue_script( 'simdiaw-box-fr-js', plugin_dir_url( __FILE__ ) . 'pickadate/lib/translations/'.WPLANG.'.js' );
    }
}
add_action( 'admin_enqueue_scripts', 'simdiaw_date_time_picker_enqueue' );

/* The options */
require_once( dirname( __FILE__ ) . '/simdiaw-options.php' );

/* The widget */
require_once( dirname( __FILE__ ) . '/simdiaw-widget.php' );
