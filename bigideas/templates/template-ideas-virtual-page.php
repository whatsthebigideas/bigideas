<?php
/*
 * This file displays the Ideas xml and captures a requester domain variable from the endpoint: /ideas/requesterdomain.com
 * and adds it to the WordPress > Settings > Ideas > "Requests" setting. Requester domains are requesting their Ideas xml be 
 * appended to our Ideas xml.
 * 
 * This file displays the Ideas xml in an html table by loading it from the wordpress installation directory using js.
 */

/* Custom header to notify the requester site that request was successful. */
header('Ideas: Ideas');

/* The url of the requester requesting us to add their Ideas xml to ours was captured from the endpoint and now available. */
$requester_domain = get_query_var('requester_domain');

/* First, check if the enpoint variable is present and valid, then start the session. */
if (filter_var(gethostbyname($requester_domain), FILTER_VALIDATE_IP))    {

    if (! session_id()) {
        session_start();

        /* Create your requests array in session scope if it does not yet exist. */
        if (! isset($_SESSION['requests'])) {
            $_SESSION['requests'] = [];
        }

        /* Add the requester domain to 'requests' for each request in order to count the number of requests made.
         * To prevent floods, too many requests from one requester are not allowed.
         */
        array_push($_SESSION['requests'], $requester_domain);

        /* To prevent floods, restrict the number of requests to 5 per session, and restrict requester domain names that are too long. */
        if (count($_SESSION['requests']) < 5 && strlen($requester_domain) < 46) {

            /* In the database, all WP > options > settings are serialized, get_option unserializes all the settings into an array of strings.
            * All the WP > options > settings must be present to reserialize and update the WP > options > settings.
            */
            $options = get_option('ideas_settings');

            /* The wordpress option setting that holds the domains you would like to request append our Ideas xml to theirs. */
            $textarea_field_0 = $options['ideas_textarea_field_0'];

            /* wordpress options setting that holds all the domains whoes Ideas xml you want append to yours. */
            $textarea_field_2 = $options['ideas_textarea_field_2'];
            /* wordpress options setting that holds the domains requesting you to append their Ideas xml to yours. */
            $textarea_field_3 = $options['ideas_textarea_field_3'];            
            /* Convert the setting string to an array. */
            $requester_array = preg_split('/[\n\r]+/', $textarea_field_3);

            /* Limit the number of requester domains added to the settings to 100 for now. 
		     * Allow another requester domain to be added to "Requests" setting if less than 100 exist in it.        
             */
            if (count($requester_array) < 100) {

                /* Push the endpoint variable onto the options array only if the requester domain doesnt already exist in the options. */
                if (! in_array($requester_domain, $requester_array)) {
                    array_push($requester_array, $requester_domain);
                }

                // Convert the array back to a string.
                $textarea_field_3 = implode(PHP_EOL,$requester_array);

                // Build all options settings together into an array of strings.
                $ideas_options = array(
                    'ideas_textarea_field_0' => $textarea_field_0,
                    'ideas_textarea_field_2' => $textarea_field_2,
                    'ideas_textarea_field_3' => $textarea_field_3,
                );

                // Serialize the options array and update the options.
                update_option('ideas_settings', $ideas_options);
            }
        }
    }
}

?>

<?php get_header();?>

<?php 

	$this_site_url = get_site_url();
	echo "<div id='new-idea-button'><a href=" . $this_site_url . "/new-idea><button>New idea</button></a></div>";

?>

<!-- the location of the javascript must be specified -->
<script type="text/javascript"
	src='&lt;?php echo plugins_url("ideas.js", __FILE__); ?&gt;'></script>
	
<table id="ideas-table"></table>

<!-- load the javascript -->
<script type="text/javascript">
    loadDoc();
  </script>

<?php get_footer(); ?>
