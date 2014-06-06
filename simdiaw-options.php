<?php

/* ************************************************************** */
/* The options page menu item                                     */
/* ************************************************************** */

/**
 * Load up the options page
 */
if( !function_exists('simdiaw_options_add_page'))  {
	function simdiaw_options_add_page() {
		add_options_page( 
			__( 'Diary options', 'simdiaw' ), // Title for the page
			__( 'Simple Diary', 'simdiaw' ), //  Page name in admin menu
			'manage_options', //  Minimum role required to see the page
			'simdiaw_options_page', // unique identifier
			'simdiaw_options_do_page'  // name of function to display the page
		);
		add_action( 'admin_init', 'simdiaw_options_settings' );	
	}
}
add_action( 'admin_menu', 'simdiaw_options_add_page' );

/* ************************************************************** */
/* Option page creation                                           */
/* ************************************************************** */

if( !function_exists('simdiaw_options_do_page'))  {
	function simdiaw_options_do_page() {
	?>

<div class="wrap">

        <h2><?php _e( 'Diary options', 'simdiaw' ) ?></h2>  
        
        <?php 
        /*** To debug, here we can print the plugin options **/
        /* 
        echo '<pre>';
        $options = get_option( 'simdiaw_settings_options' );
        print_r($options); 
        echo '</pre>';
        */
        if ($_GET['settings-updated']) {
	        // flushing rewrite rules
	        simdiaw_reminders_custom_init();
            flush_rewrite_rules();
       }
         ?>
        
        <form method="post" action="options.php">
        		<?php settings_fields( 'simdiaw_settings_options' ); ?>
		  	<?php do_settings_sections('simdiaw_setting_section'); ?>
		  	<p><input class="button-primary"  name="Submit" type="submit" value="<?php esc_attr_e(__('Save Changes','simdiaw')); ?>" /></p>		
        </form>
        <script>
        jQuery(document).ready(function() {
            
        });
        </script>
</div>

<?php
	} // end simdiaw_options_do_page
}


/* ************************************************************** */
/* The options creation and managing                              */
/* ************************************************************** */

/**
 * Init plugin options to white list our options
 */
if( !function_exists('simdiaw_options_settings'))  {
	function simdiaw_options_settings(){
		/* Register simdiaw settings. */
		register_setting( 
			'simdiaw_settings_options',  //$option_group , A settings group name. Must exist prior to the register_setting call. This must match what's called in settings_fields()
			'simdiaw_settings_options', // $option_name The name of an option to sanitize and save.
			'simdiaw_options_validate' // $sanitize_callback  A callback function that sanitizes the option's value.
			);

		/** Add a section **/
		add_settings_section(
			'simdiaw_option_main', //  section name unique ID
			'&nbsp;', // Title or name of the section (to be output on the page), you can leave nbsp here if not wished to display
			'simdiaw_option_section_text',  // callback to display the content of the section itself
			'simdiaw_setting_section' // The page name. This needs to match the text we gave to the do_settings_sections function call 
			);

		/** Register each option **/
		add_settings_field(
			'simdiaw_title', 
			__( 'Diary page title', 'simdiaw' ), 
			'simdiaw_func_title', 
			'simdiaw_setting_section',  
			'simdiaw_option_main' 
			);
			
		add_settings_field(
			'simdiaw_slug', 
			__( 'Slug', 'simdiaw' ), 
			'simdiaw_func_slug', 
			'simdiaw_setting_section',  
			'simdiaw_option_main' 
			); 
			
         add_settings_field(
			'upcoming_reminders_count', 
			__( 'Upcoming reminders count', 'simdiaw' ), 
			'simdiaw_func_u_r_count', 
			'simdiaw_setting_section',  
			'simdiaw_option_main' 
			);
	}
}

/** the theme section output **/
if( !function_exists('simdiaw_option_section_text'))  {
	function simdiaw_option_section_text(){
	echo '<p>'.__( 'Here you can set the options of Simple Diary plugin.', 'simdiaw' ).'</p>';
	}
}

/** The diary title **/
function simdiaw_func_title() {
	 /* Get the option value from the database. */
	$options = get_option( 'simdiaw_settings_options' );
	$simdiaw_title = ($options['simdiaw_title'] != '') ? $options['simdiaw_title'] : __('Diary', 'simdiaw') ;
	
	/* Echo the field. */ ?>
	<input type="text" id="simdiaw_title" name="simdiaw_settings_options[simdiaw_title]" value="<?php echo $simdiaw_title ?>" />
		<p class="description">
		    <?php _e( 'The title that will be displayed on top of diary pages.', 'simdiaw' ); ?>
        </p>
<?php }

/** The diary slug **/
function simdiaw_func_slug() {
	 /* Get the option value from the database. */
	$options = get_option( 'simdiaw_settings_options' );
	$simdiaw_slug = ($options['simdiaw_slug'] != '') ? $options['simdiaw_slug'] : __('diary', 'simdiaw') ;
	
	/* Echo the field. */ ?>
	<input type="text" id="simdiaw_slug" name="simdiaw_settings_options[simdiaw_slug]" value="<?php echo $simdiaw_slug ?>" />
		<p class="description">
		    <?php printf(__( 'The slug that is used to identify the reminders page. This slug must be URL friendly so only lowercase characters, numbers and "-" are allowed (no spaces, no accentuated characters). If you defined your permalink to have nice URL in the <a href="options-permalink.php">Permalink option page</a> you can get the Diary page on <code>%1$s</code>. If not, this page will be available on <code>%2$s</code>.', 'simdiaw' ), home_url( '/' ).$simdiaw_slug, home_url( '/' ).'?post_type=reminder'); ?>
        </p>
<?php }

/** The number of upcoming reminders that should be displayed by default  **/
function simdiaw_func_u_r_count() {
	 /* Get the option value from the database. */
	$options = get_option( 'simdiaw_settings_options' );
	$upcoming_reminders_count = ($options['upcoming_reminders_count'] != '') ? $options['upcoming_reminders_count'] : 3 ;
	
	/* Echo the field. */ ?>
	<input size="3" type="number" id="upcoming_reminders_count" name="simdiaw_settings_options[upcoming_reminders_count]" value="<?php echo $upcoming_reminders_count ?>" />
		<p class="description">
		    <?php _e( 'Default number of upcoming reminders that should be displayed when using for exemple handcrafted sidebar element with <code>the_simdiaw_upcoming_reminders()</code> function. Will be overriden by value set in Simple Diary widget or in the template function <code>the_simdiaw_upcoming_reminders()</code> (ex: <code>the_simdiaw_upcoming_reminders(5)</code> will display the next 5 upcoming reminders).', 'simdiaw' ); ?>
        </p>
<?php }

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
if( !function_exists('simdiaw_options_validate'))  {
	function simdiaw_options_validate( $input ) {
	    $options = get_option( 'simdiaw_settings_options' );
	    
	     /** Title validation **/
	     if ($options['simdiaw_title'] != ($input['simdiaw_title'])) {
	        $options['simdiaw_title'] = ($input['simdiaw_title'] != '') ? wp_filter_nohtml_kses($input['simdiaw_title']) : __('Diary', 'simdiaw');
        }
	    
	    /** Slug validation **/
	    if ($options['simdiaw_slug'] != ($input['simdiaw_slug'])) {
	        $options['simdiaw_slug'] = ($input['simdiaw_slug'] != '') ? urlencode($input['simdiaw_slug']) : __('diary', 'simdiaw');
        }
        
        /** upcoming reminders count validation **/
        if ($options['upcoming_reminders_count'] != ($input['upcoming_reminders_count'])) {
            $options['upcoming_reminders_count'] = ($input['upcoming_reminders_count'] > 0) ? wp_filter_nohtml_kses(intval($input['upcoming_reminders_count'])) : 3;
        }
	    return $options;	
	}
}
