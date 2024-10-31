<?php
/*
Plugin Name:   Portfolio With Filter
Plugin URI : https://profiles.wordpress.org/shail25/
Description:  Custom Post type Portfolio with Filter , Portfolio items Filter Tabwise & without page refresh
Version: 1.0.0
Author: Shail Mehta
Text Domain: wporg
Author URL : https://profiles.wordpress.org/shailu25/
*/

defined('ABSPATH') or die('Hey, what are you doing here?');

if (!class_exists('portfolio_with_filter')) {
    class Portfolio_with_filter
    {
        function portfolio_with_filter_install()
        {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }

        function activate()
        {
            flush_rewrite_rules();
        }

        function deactivate()
        {
            flush_rewrite_rules();
        }
    }


// activation and deactivation hook
    $portfolio_with_filter = new Portfolio_with_filter();
    register_activation_hook(__FILE__, array($portfolio_with_filter, 'activate'));
    register_deactivation_hook(__FILE__, array($portfolio_with_filter, 'deactivate'));


    add_action('init', 'portfolio_with_filter_enqueue');
    function portfolio_with_filter_enqueue()
    {
        // enqueue style
        wp_enqueue_style('Portfolio Style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), false, 'all');
        wp_enqueue_script('Filterable Script', plugin_dir_url(__FILE__) . 'assets/js/filterable.js', array('jquery'), '1.1', true);

    }


    add_action('init', 'portfolio_with_filter_enqueue_init');


    /*-- Custom Post type Portfolio Init Begin --*/
    function portfolio_with_filter_enqueue_init()
    {
        $labels = array(
            'name' => _x('Portfolios', 'post type general name'),
            'singular_name' => _x('Portfolio', 'post type singular name'),
            'add_new' => _x('Add New', 'portfolio'),
            'add_new_item' => __('Add New Portfolio'),
            'edit_item' => __('Edit Portfolio'),
            'new_item' => __('New Portfolio'),
            'view_item' => __('View Portfolio'),
            'search_items' => __('Search Portfolios'),
            'not_found' => __('No portfolios found'),
            'not_found_in_trash' => __('No portfolios found in Trash'),
            'parent_item_colon' => '',
            'menu_name' => 'Portfolio'

        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
        );
        // The following is the main step where we register the post.
        register_post_type('portfolio', $args);

        // Initialize New Taxonomy Labels
        $labels = array(
            'name' => _x('Tags', 'taxonomy general name'),
            'singular_name' => _x('Tag', 'taxonomy singular name'),
            'search_items' => __('Search Types'),
            'all_items' => __('All Tags'),
            'parent_item' => __('Parent Tag'),
            'parent_item_colon' => __('Parent Tag:'),
            'edit_item' => __('Edit Tags'),
            'update_item' => __('Update Tag'),
            'add_new_item' => __('Add New Tag'),
            'new_item_name' => __('New Tag Name'),
        );
        // Custom taxonomy for Portfolio Tags
        register_taxonomy('tagportfolio', array('portfolio'), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tag-portfolio'),
        ));

    }

    function portfolio_with_filter_func($atts)
    {
        ?>
        <?php
        $terms = get_terms("tagportfolio");
        $count = count($terms);
        echo '<ul id="portfolio-filter">';
        echo '<li><a href="#all" title="">All</a></li>';
        if ($count > 0) {
            foreach ($terms as $term) {

                $termname = strtolower($term->name);
                $termname = str_replace(' ', '-', $termname);
                echo '<li><a href="#' . $termname . '" title="" rel="' . $termname . '">' . $term->name . '</a></li>';
            }
        }
        echo "</ul>";
        ?>

        <?php
        $loop = new WP_Query(array('post_type' => 'portfolio', 'posts_per_page' => -1));
        $count = 0;
        ?>

        <div id="portfolio-wrapper">
            <ul id="portfolio-list">

                <?php if ($loop) :
                    while ($loop->have_posts()) : $loop->the_post(); ?>
                        <?php
                        global $post;
                        $terms = get_the_terms($post->ID, 'tagportfolio');

                        if ($terms && !is_wp_error($terms)) :
                            $links = array();

                            foreach ($terms as $term) {
                                $links[] = $term->name;
                            }
                            $links = str_replace(' ', '-', $links);
                            $tax = join(" ", $links);
                        else :
                            $tax = '';
                        endif;
                        ?>

                        <?php $infos = get_post_custom_values('_url'); ?>

                        <li class="portfolio-item <?php echo strtolower($tax); ?> all">
                            <div class="thumb"><a
                                        href="<?php the_permalink() ?>"><?php the_post_thumbnail('medium'); ?></a>
                            </div>
                            <p class="portfolio-title"><a class="title-link-none"
                                                          href="<?php the_permalink() ?>"><?php the_title(); ?></a></p>
                        </li>

                    <?php endwhile; else: ?>

                    <li class="error-not-found">Sorry, no portfolio entries for while.</li>

                <?php endif; ?>


            </ul>
            <div class="clearboth"></div>
        </div>


    <?php }

    add_shortcode('filter-portfolio', 'portfolio_with_filter_func');
}

