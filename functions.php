<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

/**
 * If you are installing Timber as a Composer dependency in your theme, you'll need this block
 * to load your dependencies and initialize Timber. If you are using Timber via the WordPress.org
 * plug-in, you can safely delete this block.
 */
$composer_autoload = __DIR__ . "/vendor/autoload.php";
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
    $timber = new Timber\Timber();
}

/**
 * This ensures that Timber is loaded and available as a PHP class.
 * If not, it gives an error message to help direct developers on where to activate
 */
if (!class_exists("Timber")) {
    add_action("admin_notices", function () {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' .
            esc_url(admin_url("plugins.php#timber")) .
            '">' .
            esc_url(admin_url("plugins.php")) .
            "</a></p></div>";
    });

    add_filter("template_include", function ($template) {
        return get_stylesheet_directory() . "/static/no-timber.html";
    });
    return;
}

/**
 * Sets the directories (inside your theme) to find .twig files
 */
Timber::$dirname = ["templates", "views"];

/**
 * By default, Timber does NOT autoescape values. Want to enable Twig's autoescape?
 * No prob! Just set this value to true
 */
Timber::$autoescape = false;

/**
 * We're going to configure our theme inside of a subclass of Timber\Site
 * You can move this to its own file and include here via php's include("MySite.php")
 */
class StarterSite extends Timber\Site
{
    /** Add timber support. */
    public function __construct()
    {
        add_action("after_setup_theme", [$this, "theme_supports"]);
        add_filter("timber/context", [$this, "add_to_context"]);
        add_filter("timber/twig", [$this, "add_to_twig"]);
        add_action("init", [$this, "register_post_types"]);
        add_action("init", [$this, "register_taxonomies"]);
        parent::__construct();
    }
    /** This is where you can register custom post types. */
    public function register_post_types()
    {
    }
    /** This is where you can register custom taxonomies. */
    public function register_taxonomies()
    {
    }

    /** This is where you add some context
     *
     * @param string $context context['this'] Being the Twig's {{ this }}.
     */
    public function add_to_context($context)
    {
        $context["custom_logo_url"] = wp_get_attachment_image_url(
            get_theme_mod("custom_logo"),
            "full"
        );

        // Global Settings
        $context["phone_number"] = get_field("phone_number", 90);
        $context["address"] = get_field("address", 90);
        $context["facebook"] = get_field("facebook", 90);
        $context["twitter"] = get_field("twitter", 90);
        $context["linkedin"] = get_field("linkedin", 90);
        $context["instagram"] = get_field("instagram", 90);
        $context["yelp"] = get_field("yelp", 90);

        $context["menu"] = new Timber\Menu();
        $context["site"] = $this;
        return $context;
    }

    public function theme_supports()
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support("automatic-feed-links");

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support("title-tag");

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support("post-thumbnails");

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support("html5", [
            "comment-form",
            "comment-list",
            "gallery",
            "caption",
        ]);

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support("post-formats", [
            "aside",
            "image",
            "video",
            "quote",
            "link",
            "gallery",
            "audio",
        ]);

        add_theme_support("menus");

        add_theme_support("custom-logo");

        // Global Variables
        add_theme_support("facebook");
        add_theme_support("twitter");
        add_theme_support("linkedin");
        add_theme_support("instagram");
        add_theme_support("yelp");
        add_theme_support("phone_number");
        add_theme_support("address");
    }

    /** This is where you can add your own functions to twig.
     *
     * @param string $twig get extension.
     */
    public function add_to_twig($twig)
    {
        $twig->addExtension(new Twig\Extension\StringLoaderExtension());
        $twig->addFilter(new Twig\TwigFilter("myfoo", [$this, "myfoo"]));
        return $twig;
    }
}

//Remove Gutenberg
add_filter("use_block_editor_for_post", "__return_false", 10);

function hide_settings_page($query)
{
    if (!is_admin()) {
        return;
    }
    global $typenow;
    if ($typenow === "page") {
        $settings_page = get_page_by_path("global-settings", null, "page")->ID;
        $query->set("post__not_in", [$settings_page]);
    }
    return;
}

add_action("pre_get_posts", "hide_settings_page");

// Add the page to admin menu
function add_site_settings_to_menu()
{
    add_menu_page(
        "Global Settings",
        "Global Settings",
        "manage_options",
        "post.php?post=" .
            get_page_by_path("global-settings", null, "page")->ID .
            "&action=edit",
        "",
        "dashicons-admin-tools",
        20
    );
}
add_action("admin_menu", "add_site_settings_to_menu");

// Change the active menu item
add_filter("parent_file", "higlight_custom_settings_page");

function higlight_custom_settings_page($file)
{
    global $parent_file;
    global $pagenow;
    global $typenow, $self;

    $settings_page = get_page_by_path("global-settings", null, "page")->ID;

    $post = (int) $_GET["post"];
    if ($pagenow === "post.php" && $post === $settings_page) {
        $file = "post.php?post=$settings_page&action=edit";
    }
    return $file;
}

function edit_site_settings_title()
{
    global $post, $title, $action, $current_screen;
    if (
        isset($current_screen->post_type) &&
        $current_screen->post_type === "page" &&
        $action == "edit" &&
        $post->post_name === "site-settings"
    ) {
        $title = $post->post_title . " - " . get_bloginfo("name");
    }
    return $title;
}

add_action("admin_title", "edit_site_settings_title");

require_once "php/reviews.php";

function smartwp_remove_wp_block_library_css()
{
    wp_dequeue_style("wp-block-library");
    wp_dequeue_style("wp-block-library-theme");
    wp_dequeue_style("wc-blocks-style"); // Remove WooCommerce block CSS
}
add_action("wp_enqueue_scripts", "smartwp_remove_wp_block_library_css", 100);

/**
 * Disable the emoji's
 */
function disable_emojis()
{
    remove_action("wp_head", "print_emoji_detection_script", 7);
    remove_action("admin_print_scripts", "print_emoji_detection_script");
    remove_action("wp_print_styles", "print_emoji_styles");
    remove_action("admin_print_styles", "print_emoji_styles");
    remove_filter("the_content_feed", "wp_staticize_emoji");
    remove_filter("comment_text_rss", "wp_staticize_emoji");
    remove_filter("wp_mail", "wp_staticize_emoji_for_email");
    add_filter("tiny_mce_plugins", "disable_emojis_tinymce");
    add_filter(
        "wp_resource_hints",
        "disable_emojis_remove_dns_prefetch",
        10,
        2
    );
}
add_action("init", "disable_emojis");

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param array $plugins
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, ["wpemoji"]);
    } else {
        return [];
    }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch($urls, $relation_type)
{
    if ("dns-prefetch" == $relation_type) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters(
            "emoji_svg_url",
            "https://s.w.org/images/core/emoji/2/svg/"
        );

        $urls = array_diff($urls, [$emoji_svg_url]);
    }

    return $urls;
}

new StarterSite();
