<?php
/**
 * Theme Support
 */
add_theme_support('title-tag');
add_theme_support('custom-logo');

function theme_enqueue_styles()
{
    wp_enqueue_style('theme-styles', get_stylesheet_uri()); // This is where you enqueue your theme's main stylesheet
    $custom_css = theme_get_customizer_css();
    wp_add_inline_style('theme-styles', $custom_css);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

function theme_scripts()
{
    wp_enqueue_script('jquery');
}

add_action('wp_enqueue_scripts', 'theme_scripts');

//Verhindert das Laden von WP Emoji
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

function add_custom_sizes()
{
    add_theme_support('post-thumbnails');
    add_image_size('small-thumbnail', 260, 200, true);
    add_image_size('nocrop-thumbnail', 260);
    add_image_size('full-image', 700);
    add_image_size('card', 400, 300, true);
    add_image_size('banner-image', 2000, 500, array('left', 'center'));
    add_image_size('small-banner-image', 700, 300, array('left', 'center'));
}

add_action('after_setup_theme', 'add_custom_sizes');

//Bildgroessen zur Auswahl hinzufuegen
function guru20_bildgroessen_auswaehlen($sizes)
{
    $custom_sizes = array('person' => 'Personen Portrait');
    return array_merge($sizes, $custom_sizes);
}

add_filter('image_size_names_choose', 'guru20_bildgroessen_auswaehlen');

if (function_exists('register_sidebar'))
    register_sidebar();

function register_my_menus()
{
    register_nav_menus(
        array(
            'main-menu' => __('Hauptnavigation'),
            'social-menu' => __('Social Links')
        )
    );
}

add_action('init', 'register_my_menus');

function addCustomField($wp_customize, $name, $title, $type) {
    // Add Section
    $wp_customize->add_section($name, array(
        'title' => $title,
        'panel' => 'text_blocks',
        'priority' => 10
    ));

    // Add setting
    $wp_customize->add_setting($name . '_block', array(
        'default' => __(''),
        'sanitize_callback' => 'sanitize_text'
    ));

    // Add control
    $wp_customize->add_control(new WP_Customize_Control(
            $wp_customize,
            $name,
            array(
                'label' => $title,
                'section' => $name,
                'settings' => $name . '_block',
                'type' => $type
            )
        )
    );
}

/*
 * Register Our Customizer Stuff Here
 */
function register_theme_customizer($wp_customize)
{
    // Create custom panel.
    $wp_customize->add_panel('text_blocks', array(
        'priority' => 500,
        'theme_supports' => '',
        'title' => __('Texte'),
        'description' => __('Set editable text for certain content.'),
    ));

    // Add Text
    addCustomField($wp_customize, 'impressum_text', __('Impressum'),'textarea');
    addCustomField($wp_customize, 'instagram_link', __('Instagram Link'),'link');
    addCustomField($wp_customize, 'twitter_link', __('Twitter Link'),'link');
    addCustomField($wp_customize, 'facebook_link', __('Facebook Link'),'link');
    addCustomField($wp_customize, 'footer_adresse', __('Footer Adresse'),'textarea');
    addCustomField($wp_customize, 'footer_links', __('Footer Links'),'textarea');


    // Accent color
    $wp_customize->add_setting('accent_color', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', array(
        'section' => 'colors',
        'label' => esc_html__('Accent color', 'theme'),
    )));

    // Main color
    $wp_customize->add_setting('main_color', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_hex_color',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'main_color', array(
        'section' => 'colors',
        'label' => esc_html__('Main color', 'theme'),
    )));
}

add_action('customize_register', 'register_theme_customizer');

// Sanitize text
function sanitize_text($text)
{
    $allowed_html = array(
        'a' => array(
            'href' => array(),
            'class' => array(),
        ),
        'br' => array(),
        'p' => array(
            'class' => array(),
        ),
        //formatting
        'strong' => array(),
        'em' => array(),
        'b' => array(),
        'i' => array(
            'class' => array(),
        ),
        'h3' => array(),
        'h4' => array(),
        'img' => array(
            'src' => array(),
            'alt' => array(),
            'class' => array(),
        ),
    );
    return wp_kses($text, $allowed_html);
}

// Modify our styles registration like so


function theme_get_customizer_css()
{
    ob_start();

    $accent_color = get_theme_mod('accent_color', '');
    if (!empty($accent_color)) {
        ?>
        #footer {
        background: <?php echo $accent_color; ?> !important;
        }
        <?php
    }

    $main_color = get_theme_mod('main_color', '');
    if (!empty($main_color)) {
        ?>
        h1, h2, h4, a #cssmenu ul li.current_page_item > span a, .abstract p, #cssmenu ul li.current-menu-ancestor > span a, #cssmenu ul li.current-menu-item > span a, #cssmenu ul li.current-menu-parent > span a, .nav-link:hover, .text a, a.link, #footer-blog-post .text-part p {
            color: <?php echo $main_color; ?> !important;
        }

        .card-box .card-box-action, .button {
        background: <?php echo $main_color; ?> !important;
        }

        .menu-item-has-children > ul.nav-expand-content::before {
        border-bottom: solid 6px <?php echo $main_color; ?> !important;
        }

        .menu-item-has-children > ul.nav-expand-content {
        border-top: 5px solid <?php echo $main_color; ?> !important;
        }
        <?php
    }
    $css = ob_get_clean();
    return $css;
}


/**
 * Shortcodes
 */
function pageLink_func($atts)
{
    $a = shortcode_atts(array(
        'id' => 0,
        'title' => 'Hier Seitentitel hinzufügen',
        'desc' => 'Hier Beschreibung hinnzufügen'
    ), $atts);

    if (has_post_thumbnail($a['id'])) {
        $img = get_the_post_thumbnail($a['id'], 'card');
    } else {
        $img = '<img src="https://via.placeholder.com/400x300">';
    }
    return '<article class="cardW pageLink">
			<div class="post-thumbnail">
				<a href="https://webtheke.ch/?p=' . $a['id'] . '">
					' . $img . '
					<h4>' . $a['title'] . '</h4>
					<div class="content">
						' . $a['desc'] . '
					</div>
				</a>
			</div>
		</article>';
}

add_shortcode('pageLink', 'pageLink_func');


/**
 * Anpassungen Worpdress
 */
//Limit max menu depth in admin panel to 3
function q242068_limit_depth($hook)
{
    if ($hook != 'nav-menus.php') return;

    // override default value right after 'nav-menu' JS
    wp_add_inline_script('nav-menu', 'wpNavMenu.options.globalMaxDepth = 2;', 'after');
}

add_action('admin_enqueue_scripts', 'q242068_limit_depth');

/*
* === CSS Mobile Menu ====
*/

class CSS_Menu_Maker_Walker extends Walker
{
    var $db_fields = array('parent' => 'menu_item_parent', 'id' => 'db_id');

    function start_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class='nav-expand-content'>\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        global $wp_query;
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        $class_names = $value = '';
        $classes = empty($item->classes) ? array() : (array)$item->classes;

        /* Add active class */
        if (in_array('current-menu-item', $classes)) {
            $classes[] = 'active';
            unset($classes['current-menu-item']);
        }

        /* Check for children */
        $children = get_posts(array('post_type' => 'nav_menu_item', 'nopaging' => true, 'numberposts' => 1, 'meta_key' => '_menu_item_menu_item_parent', 'meta_value' => $item->ID));
        if (!empty($children)) {
            $classes[] = 'has-sub';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $value . $class_names . '>';

        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

        $item_output = $args->before;
        $item_output .= '<span><a class="nav-link" ' . $attributes . '>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a></span>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $output .= "</li>\n";
    }
}

add_action('admin_notices', 'my_theme_dependencies');

function my_theme_dependencies()
{
    if (!function_exists('block_lab'))
        echo '<div class="error"><p>' . __('Achtung: Das Theme benötigt Block Lab um zu funkionieren.', 'my-theme') . '</p></div>';
}

?>
