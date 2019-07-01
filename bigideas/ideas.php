<?php

/**
 * ******************************************************************************************************************************* 
 * @link              bigideas
 * @since             1.0.0
 * @package           BigIdeas
 *
 * @wordpress-plugin
 * Plugin Name:       BigIdeas
 * Plugin URI:        bigideas
 * Description:       Allows a user to post an idea to an Ideas page at /Ideas/. A BuddyPress group with bbPress forum are 
 *                    automatically created when this post is published.
 * Version:           1.0.0
 * Author:            WhatsTheBigIdea
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bigideas
 * Domain Path:       /languages
 *
 * Ideas - See the README.txt in the Ideas plugin directory for instructions.
 *
 * The boilerplate code is from the generator at: https://wppb.me.
 * Because the Ideas plugin requires BuddyPress, bbPress, and User Submitted Posts plugins,
 * the TGM plugin activation library is used: http://tgmpluginactivation.com.
 * Virtual page code from here is used: https://github.com/Nabesaka/wp-virtual-page-tutorial.
 * *******************************************************************************************************************************
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die();
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PLUGIN_NAME_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ideas-activator.php
 */
function activate_ideas()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ideas-activator.php';
    Ideas_Activator::activate();

    // Create the Ideas category
    wp_create_category('Ideas');

    // Create an "Ideas" page to reserve the virtual page slug /ideas/
    $ideas_page = array(
        'post_title' => 'Ideas',
        'post_name' => 'ideas',
        'post_status' => 'pending',
        'post_type' => 'page',
        'post_author' => 1
    );

    wp_insert_post($ideas_page);

    // Create a "New Idea" page to hold the User Submitted Posts short code
    $usp_page = array(
        'post_title' => 'New Idea',
        'post_name' => 'new-idea',
        'post_status' => 'pending',
        'post_type' => 'page',
        'post_author' => 1,
        'post_content' => '[user-submitted-posts]'
    );

    wp_insert_post($usp_page);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ideas-deactivator.php
 */
function deactivate_ideas()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-ideas-deactivator.php';
    Ideas_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ideas');
register_deactivation_hook(__FILE__, 'deactivate_ideas');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ideas.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_ideas()
{
    $plugin = new Ideas();
    $plugin->run();
}
run_ideas();

/**
 * TGM plugin activation library: http://tgmpluginactivation.com
 * A drop in library: class-tgm-plugin-activation.php
 * Prompts the installation of required plugins before Ideas is installed.
 */
require_once plugin_dir_path(__FILE__) . '/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'ideas_register_required_plugins');

function ideas_register_required_plugins()
{
    $plugins = array(

        array(
            'name' => 'BuddyPress',
            'slug' => 'buddypress',
            'required' => true
        ),

        array(
            'name' => 'User Submitted Posts',
            'slug' => 'user-submitted-posts',
            'required' => true
        ),

        array(
            'name' => 'bbPress',
            'slug' => 'bbpress',
            'required' => true
        )
    );

    $config = array(

        'id' => 'ideas', // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '', // Default absolute path to bundled plugins.
        'menu' => 'tgmpa-install-plugins', // Menu slug.
        'parent_slug' => 'plugins.php', // Parent menu slug.
        'capability' => 'manage_options', // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices' => true, // Show admin notices or not.
        'dismissable' => true, // If false, a user cannot dismiss the nag message.
        'dismiss_msg' => '', // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false, // Automatically activate plugins after installation or not.
        'message' => '' // Message to output right before the plugins table.
    );

    tgmpa($plugins, $config);
}

/**
 * *******************************************************************************************************************************
 * Ideas
 * *******************************************************************************************************************************
 */

/**
 * Helper function
 * Remove 'http://' or 'https://'
 */
function ideas_remove_http_s($string)
{
    $find = array(
        'http://',
        'https://'
    );
    $replace = '';

    $clean = str_replace($find, $replace, $string);

    return $clean;
}

/**
 * Helper function
 * Clean multiple spaces from $title
 * Limit to 50 characters
 */
function ideas_get_clean_title()
{
    $title = get_the_title();
    $clean_title = preg_replace('!\s+!', ' ', $title);
    $short_clean_title = substr($clean_title, 0, 50);

    return $short_clean_title;
}

/**
 * Helper function
 * Clean multiple newlines and multiple spaces from $content
 */
function ideas_get_clean_content()
{
    $content = get_the_content();
    $str = preg_replace("/\n+/", "\n", $content);
    $clean_content = preg_replace('!\s+!', ' ', $str);

    return $clean_content;
}

/**
 * Helper function
 * removes whitespace characters from end of post
 * before it is saved to the database
 */
add_filter('content_save_pre', 'trim');

/**
 * Helper function
 * Exclude posts of category "Ideas" from the posts page.
 * (Ideas posts are displayed on the /ideas/ page.)
 */
add_filter('pre_get_posts', 'exclude_ideas_category');

function exclude_ideas_category($query)
{
    $cat_id = get_cat_ID('Ideas');

    if ($query->is_home()) {
        $query->set('cat', - $cat_id);
    }
    return $query;
}

/**
 * Helper function for ideas_testing.
 * Tests the output of various variables.
 */
function ideas_testing($an_array)
{

    /**
     * Output tests for plugin path and urls
     */
    $file = "ideas_testing.txt";

    $abspath = ABSPATH;

    $plugin_dir_path = plugin_dir_path(__FILE__);

    $testing_dir_path = $plugin_dir_path . "/testing/";

    $abspath_wp_dir_ideas_xml_path = ABSPATH . "ideas.xml";

    /* gives the path to the file */
    $plugin_js_url = plugins_url("ideas/js/ideas.js");
    /* this plugin has its own style.css, so this is not used */
    $stylesheet_uri = get_stylesheet_directory_uri();
    $template_uri = get_template_directory_uri();

    $text = "########################################################################" . PHP_EOL;
    $text .= "ABSPATH wp dir ideas.xml_path: " . $abspath_wp_dir_ideas_xml_path . PHP_EOL;
    $text .= "ABSPATH: " . $abspath . PHP_EOL;
    $text .= "path to plugin: " . $plugin_dir_path . PHP_EOL;
    $text .= "javascript url: " . $plugin_js_url . PHP_EOL;
    $text .= "stylesheet uri: " . $stylesheet_uri . PHP_EOL;
    $text .= "template uri: " . $template_uri . PHP_EOL;
    $text .= "########################################################################" . PHP_EOL;

    $arrlength = sizeof($an_array);
    for ($x = 0; $x < $arrlength; $x ++) {
        $text .= $an_array[$x] . PHP_EOL;
    }

    $fh = fopen($testing_dir_path . $file, "w") or die("can't open file");
    fwrite($fh, $text);
    fclose($fh);
}

/**
 * ****************************************************************************************************************
 * Creates a virtual page at /ideas/ for displaying the Ideas xml.
 * Creates a Virtual Page in WordPress while keeping the theme intact.
 * The virtual page code is from: https://github.com/Nabesaka/wp-virtual-page-tutorial.
 * ****************************************************************************************************************
 */
class Ideas_Virtual_Page
{

    public function __construct()
    {
        register_activation_hook(__FILE__, array(
            $this,
            'activate'
        ));

        add_action('init', array(
            $this,
            'rewrite'
        ));

        add_filter('query_vars', array(
            $this,
            'query_vars'
        ));

        add_action('template_include', array(
            $this,
            'change_template'
        ));
    }

    public function activate()
    {
        set_transient('vpt_flush', 1, 60);
    }

    public function rewrite()
    {
        add_rewrite_rule('^ideas$', 'index.php?ideas-virtual-page=1', 'top');

        if (get_transient('vpt_flush')) {
            delete_transient('vpt_flush');
            flush_rewrite_rules();
        }
    }

    public function query_vars($vars)
    {
        $vars[] = 'ideas-virtual-page';

        return $vars;
    }

    public function change_template($template)
    {
        if (get_query_var('ideas-virtual-page', false) !== false) {

            // Check theme directory first
            $new_template = locate_template(array(
                'template-ideas-virtual-page.php'
            ));
            if ('' != $new_template) {
                return $new_template;
            }

            // Check plugin directory next
            $new_template = plugin_dir_path(__FILE__) . 'templates/template-ideas-virtual-page.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }

        // Fall back to original template
        return $template;
    }
}

new Ideas_Virtual_Page();

/**
 * *******************************************************************************************************************
 * Another site running Ideas can request for their Ideas xml to be added to our Ideas xml.
 *
 * Thus, register a new query variable to hold the outside domain making the request.
 *
 * Add a rewrite rule to create an endpoint so the request domain name variable can be captured from the
 * url endpoint ie.: /ideas/some-requester-domain.com, and then added to the wp query variables and then
 * retrieved in the virtual page code.
 * *******************************************************************************************************************
 */
add_filter('query_vars', 'ideas_add_query_vars');

function ideas_add_query_vars($vars)
{
    $vars[] = "requester_domain";
    return $vars;
}

/**
 * Adds a rewrite rule to handle a request from a requester domain to add their Ideas xml to ours.
 */
add_action('init', 'ideas_requester_domain_rewrite_rule');

function ideas_requester_domain_rewrite_rule()
{
    add_rewrite_rule('^ideas/([^/]+)/?$', 'index.php?ideas-virtual-page=1&requester_domain=$matches[1]', 'top');
}

/**
 * ****************************************************************************************************************
 * Enqueue the ideas virtual page stylesheet
 * *****************************************************************************************************************
 */
add_action('wp_enqueue_scripts', 'enqueue_ideas_stylesheet');

function enqueue_ideas_stylesheet()
{
    wp_enqueue_style('ideas_css', plugins_url('style.css', __FILE__));
}

/**
 * ****************************************************************************************************************
 * When publish_post of category Ideas, create a BuddyPress group with a bbPress forum, labeled with the post title.
 * *****************************************************************************************************************
 */
add_action('publish_post', 'ideas_create_group_with_forum', 10, 2);

function ideas_create_group_with_forum($ID, $post)
{
    $postcat = get_the_category($post->ID);
    $category = '';

    if (! empty($postcat)) {
        $category = $postcat[0]->name;
    }

    if ($category == 'Ideas') {

        /*
         * Create a buddyPess group
         */
        $short_clean_title = ideas_get_clean_title();

        $new_group = new BP_Groups_Group();

        $new_group->creator_id = 1;
        $new_group->name = $short_clean_title;
        $new_group->slug = strtolower($short_clean_title);

        /*
         * The helper function: ideas_get_clean_content() causes a blank to appear in the group > description, thus
         * "get_post_field('post_content', $post->ID)" is used here instead.
         * Further, the group > description is better without clean content since newlines and multiple space are
         * desirable.
         */
        $new_group->description = 'Welcome to ' . $short_clean_title . " >> " . get_post_field('post_content', $post->ID);
        $new_group->status = 'public';
        $new_group->enable_forum = 1;
        $new_group->enable_photos = 0;
        $new_group->photos_admin_only = 1;
        $new_group->date_created = current_time('mysql');
        $new_group->total_member_count = 0;

        $saved = $new_group->save();

        if ($saved) {

            $short_clean_title = ideas_get_clean_title();
            $title_with_dashes = str_replace(" ", "-", $short_clean_title);
            $siteLink = get_site_url();

            /* Send an email to notify the user who submitted the Idea that it is posted and the Idea group created. */
            $author = $post->post_author; /* Post author ID. */
            $name = get_the_author_meta('display_name', $author);
            $email = get_the_author_meta('user_email', $author);
            $title = $post->post_title;
            $permalink = $siteLink . "/groups/" . strtolower($title_with_dashes);
            $to[] = sprintf('%s <%s>', $name, $email);
            $subject = sprintf('Published: %s', $title);
            $message = sprintf('Congratulations, %s! Your Idea “%s” has been published.' . "\n\n", $name, $title);
            $message .= sprintf('View: %s', $permalink);
            $headers[] = '';
            wp_mail($to, $subject, $message, $headers);

            $id = $new_group->id;

            /*
             * Insert a new bbPress forum into posts using the post title.
             */
            $forum_data = array(
                'post_title' => $title
            );
            $forum_id = bbp_insert_forum($forum_data);

            groups_update_groupmeta($id, 'forum_id', $forum_id);
            groups_update_groupmeta($id, 'total_member_count', 0);
            groups_update_groupmeta($id, 'last_activity', time());
            groups_update_groupmeta($id, 'theme', 'buddypress');
            groups_update_groupmeta($id, 'stylesheet', 'buddypress');
        } else {
            return false;
        }
    }
}

/**
 * ****************************************************************************************************************
 * Build ideas.xml file.
 * WP_Query for posts of category "Ideas", and build the BuddyPress group url using the post title, and the donate url.
 * *****************************************************************************************************************
 */
add_action('publish_post', 'create_ideas_xml', 10, 2);

function create_ideas_xml()
{
    $plugin_dir = plugin_dir_path(__FILE__);
    $ideas_file = "ideas.xml";
    $fh = fopen($plugin_dir . $ideas_file, "w") or die("can't open file");

    $ideas_xml = "";
    $ideas_xml .= '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
    $ideas_xml .= '<ideas>' . PHP_EOL;

    // the query
    $wpb_all_query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => - 1,
        'category_name' => 'ideas'
    ));

    if ($wpb_all_query->have_posts()) :

        /* the loop */
        while ($wpb_all_query->have_posts()) :
            $wpb_all_query->the_post();

            $post = get_post();

            $ideas_xml .= '<idea>' . PHP_EOL;

            $short_clean_title = ideas_get_clean_title();
            $title_with_dashes = str_replace(" ", "-", $short_clean_title);
            $clean_content = ideas_get_clean_content();

            /**
             * Build the group and donate urls
             */
            $siteLink = get_site_url();
            $group_url = "<a href=" . '"' . $siteLink . "/groups/" . $title_with_dashes . '"' . ">" . $short_clean_title . " </a>";
            $ideas_xml .= '<title><![CDATA[' . $group_url . ']]></title>' . PHP_EOL;

            $donate_link = $post->usp_custom_field;
            $clean_donate_link = ideas_remove_http_s($donate_link);

            $donate_url = "<a href=" . '"' . "http://" . $clean_donate_link . '"' . ">" . $clean_donate_link . " </a>";
            $ideas_xml .= '<donate><![CDATA[' . $donate_url . ']]></donate>' . PHP_EOL;

            $ideas_xml .= '<description><![CDATA[' . substr($clean_content, 0, 138) . ']]></description>' . PHP_EOL;
            $ideas_xml .= '</idea>' . PHP_EOL;
        endwhile
        ;

        /* end of the loop */
        $ideas_xml .= '</ideas>' . PHP_EOL;
        fwrite($fh, $ideas_xml);
        fclose($fh);

        wp_reset_postdata();

    else :
        _e('Sorry, no posts matched your criteria.');
    endif;
}

/**
 * ****************************************************************************************************************
 * Javascript retrieves data from ideas.xml file and loads it into an html table displayed on the /ideas/ page.
 * Javascript is in a separate file.
 * Enqueue this Javascript file.
 * Register the Javascript.
 * Apply wp_localize_script which is used to localize the plugin url so it can be accessed from within javascript,
 * ie. ideas_js.ideas_xml_url is used to return the plugin url to the javascript.
 * *****************************************************************************************************************
 */

add_action('wp_loaded', 'register_ideas_js');

function register_ideas_js()
{
    $plugin_url = plugins_url("ideas/js/ideas.js");

    wp_register_script('ideas_js', $plugin_url, array(
        'jquery'
    ), $plugin_url);

    /* Set the location of the Ideas xml loaded by the javascript. */
    wp_localize_script('ideas_js', 'ideas_js', array(
        'ideas_xml_url' => get_site_url() . "/ideas.xml",
        'new_idea_page' => get_site_url()
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_ideas_js');

function enqueue_ideas_js()
{
    $plugin_url = plugins_url("ideas/js/ideas.js");

    wp_enqueue_script('ideas_js', $plugin_url, array(
        'jquery'
    ), $plugin_url);
}

/**
 * ****************************************************************************************************************
 * Create the wp-admin > Settings > Ideas
 * ****************************************************************************************************************
 */
add_action('admin_menu', 'ideas_add_admin_menu');
add_action('admin_init', 'ideas_settings_init');

function ideas_add_admin_menu()
{
    add_submenu_page('options-general.php', 'Ideas', 'Ideas', 'manage_options', 'ideas', 'ideas_options_page');
}

function ideas_settings_init()
{
    register_setting('plugin_page', 'ideas_settings');

    add_settings_section('ideas_plugin_page_section', __('Settings', 'ideas'), 'ideas_settings_section_callback', 'plugin_page');

    add_settings_field('ideas_textarea_field_0', __('Requests for append:</br></br>You can ask other domains to append your Ideas.', 'ideas'), 'ideas_textarea_field_0_render', 'plugin_page', 'ideas_plugin_page_section');

    add_settings_field('ideas_textarea_field_2', __('Append:</br></br>You can append the Ideas of other domains.', 'ideas'), 'ideas_textarea_field_2_render', 'plugin_page', 'ideas_plugin_page_section');

    add_settings_field('ideas_textarea_field_3', __('Requests to append:</br></br>Paste these append requests into "Append".', 'ideas'), 'ideas_textarea_field_3_render', 'plugin_page', 'ideas_plugin_page_section');

    add_settings_field('ideas_text_field_4', __('', 'ideas'), 'ideas_text_field_4_render', 'plugin_page', 'ideas_plugin_page_section');
}

function ideas_textarea_field_0_render()
{
    $options = get_option('ideas_settings');

    ?>
<textarea class="ideas-settings"
	name='ideas_settings[ideas_textarea_field_0]' cols="" rows=""><?php echo $options['ideas_textarea_field_0']; ?></textarea>
<?php
}

function ideas_textarea_field_2_render()
{
    $options = get_option('ideas_settings');
    ?>
<textarea class="ideas-settings"
	name='ideas_settings[ideas_textarea_field_2]' cols="" rows=""><?php echo $options['ideas_textarea_field_2']; ?></textarea>
<?php
}

function ideas_textarea_field_3_render()
{
    $options = get_option('ideas_settings');
    ?>
<textarea class="ideas-settings"
	name='ideas_settings[ideas_textarea_field_3]' cols="" rows=""><?php echo $options['ideas_textarea_field_3']; ?></textarea>
<?php
}

/* A hidden setting that allows the form button to work. */
function ideas_text_field_4_render()
{
    create_ideas_xml();
    /* An empty parameter is used here because the ideas_combine_all_xml() requires the $post parameter. */
    ideas_combine_all_xml('');
    ideas_build_request_urls();
    ?>
<input type='hidden' name='ideas_settings[ideas_text_field_4]' value='1'>
<?php
}

function ideas_settings_section_callback()
{
    echo __('These settings manipulate yourdomain.com/ideas.xml file which is publicly accessible. <br> Example values: www.somedomain.com or somedomain.com', 'ideas');
}

function ideas_options_page()
{
    ?>
<form action='options.php' method='post'>
	<h2>Ideas</h2>
<?php
    settings_fields('plugin_page');
    do_settings_sections('plugin_page');
    submit_button('Complete');
    echo "Complete fully updates Ideas. <br><br> ";
    echo "Thank You";
    ?>
</form>
<?php
}

/**
 * ************************************************************************************************************************
 * We can request other sites append our Ideas xml to their Ideas xml.
 * Build the request urls, and get response headers to verify request was recieved by the outside domain.
 * ************************************************************************************************************************
 */
function ideas_build_request_urls()
{
    /* Retrieve the ideas settings */
    $options = get_option('ideas_settings');

    /* From the Ideas settings, get the setting of "Request for append", the outside domains being sent requests. */
    $requests_string = $options['ideas_textarea_field_0'];

    /* Parse the "Request for append" setting string into an array. */
    $requests_array = preg_split('/[\n\r]+/', $requests_string);

    $local_domain = get_site_url();

    /*
     * The proper format for the endpoint variable in the request is: domain.topleveldomain
     * ie. endpoint: /ideas/somedomain.com. Thus, remove http:// or https://
     */
    $clean_local_domain = ideas_remove_http_s($local_domain);

    /* Build and send request urls */
    $array_length = count($requests_array);
    for ($x = 0; $x < $array_length; $x ++) {

        $outside_domain = $requests_array[$x];

        if (! empty($outside_domain)) {

            $request_url = "http://" . $outside_domain . "/ideas/" . $clean_local_domain;

            $headers = @get_headers($request_url);

            if ($headers == true) {

                if (in_array('Ideas: Ideas', $headers)) {
                    echo $outside_domain . ' : successfully received your request.<br>';
                } else {
                    echo $outside_domain . " : Ideas plugin not installed.<br>";
                }
            } else {
                echo $outside_domain . " : not reachable. <br>";
            }
        }
    }
}

/* Helper function for adding xml child elements. */
function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from)
{
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}

/**
 * ************************************************************************************************************************
 * We can append other sites Ideas xml to our Ideas xml.
 * Append our Ideas.xml as a simplexml object to a simplexml object that also has appended to it the Ideas xml from all the
 * outside domains in the Append setting.
 * Delete duplicate idea xml objects which would result from multiple ideas xml appends between domains.
 * The result is an ideas.xml file in the wp directory that contains the Ideas xml from our domain and all outside domains.
 * ************************************************************************************************************************
 */


add_action('publish_post', 'ideas_combine_all_xml', 10, 2);

function ideas_combine_all_xml($post)
{
    if (has_category('ideas', $post)) {
        
        $array = array(
            'hello22222'
        );
        ideas_testing($array);

        /* Retrieve the Ideas settings. */
        $options = get_option('ideas_settings');

        /* From the Ideas settings, get the setting for the outside domains to "Append" to our Ideas xml. */
        $appends_string = $options['ideas_textarea_field_2'];

        /* Parse the "Append" setting string into an array. */
        $domains_array = preg_split('/[\n\r]+/', $appends_string);

        /* Our Ideas xml */
        $plugin_ideas_xml = plugin_dir_path(__FILE__) . "ideas.xml";

        /* Wordpress directory Ideas xml */
        $wp_dir_ideas_xml = ABSPATH . "ideas.xml";

        /* Start building the all domains simplexml object. */
        $sxml = simplexml_load_string("<ideas></ideas>");

        /* Load our Ideas xml into a simplexml object. */
        $plugin_ideas_xml_obj = simplexml_load_file($plugin_ideas_xml);

        /* Next, append our Ideas simplexml object onto the all domains simplexml object. */
        foreach ($plugin_ideas_xml_obj->idea as $idea) {
            sxml_append($sxml, $idea);
        }

        /* Next, append the Ideas xml from all outside domains onto the all domains simplexml object. */
        $array_length = count($domains_array);
        for ($x = 0; $x < $array_length; $x ++) {

            if (! empty($domains_array[$x])) {

                $domain_xml = "http://" . $domains_array[$x] . "/ideas.xml";

                /* create curl resource */
                $ch = curl_init();

                /* set url */
                curl_setopt($ch, CURLOPT_URL, $domain_xml);

                /* return the transfer as a string */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                /* set the limit in seconds for making the connection. */
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);

                /* set the limit in seconds for total execution of curl. */
                curl_setopt($ch, CURLOPT_TIMEOUT, 14);

                /* contains the returned xml string */
                $returned_xml = curl_exec($ch);

                /* close curl resource to free up system resources */
                curl_close($ch);

                /*
                 * Check if the returned string is xml by using it to create a simplexml object.
                 * Suppress warnings output by simplexml_load_file and use our own handling instead.
                 */
                $use_errors = libxml_use_internal_errors(true);
                $outside_simplexml_object = simplexml_load_string($returned_xml);

                if ($outside_simplexml_object === false) {
                    echo $domain_xml . " is not valid <br>";
                }
                libxml_clear_errors();
                libxml_use_internal_errors($use_errors);

                if ($outside_simplexml_object) {
                    echo "sucessfully retrieved : " . $domain_xml . "." . "<br>";

                    /* Append onto the Ideas simplexml object. */
                    foreach ($outside_simplexml_object->idea as $idea) {
                        sxml_append($sxml, $idea);
                    }
                }
            }
        }

        /*
         * The Ideas all domains simplexml object is nested <idea> objects.
         * ie. <ideas><idea></idea></ideas>
         * Use xpath() to reach the <idea> level which is where duplicates will be found.
         * For each iteration, represent the object as a string and load the string into the $found array as a $key of
         * boolean value true. If the next string is found to be set in the $found array, remove the object from the xml
         * with unset.
         */
        $found = [];

        $ideaPos = 0;
        foreach ($sxml->xpath("//idea") as $key => $idea) {
            $ideaType = (string) $idea->title . "," . (string) $idea->donate . "," . (string) $idea->description;
            if (isset($found[$ideaType])) {
                unset($sxml->idea[$ideaPos]);
            } else {
                $found[$ideaType] = true;
                $ideaPos ++;
            }
        }

        $doc = new DOMDocument();
        $doc->formatOutput = TRUE;
        $doc->loadXML($sxml->asXML());
        $xml = $doc->saveXML();

        file_put_contents($wp_dir_ideas_xml, $xml);
    }
}
