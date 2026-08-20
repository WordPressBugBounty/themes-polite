<?php
/**
 * Post Navigation Function
 *
 * @since Polite 1.0.0
 *
 * @param null
 * @return void
 *
 */
if (!function_exists('polite_posts_navigation')) :
    function polite_posts_navigation()
    {
        global $polite_theme_options;
        $polite_pagination_option = $polite_theme_options['polite-pagination-options'];
        if ('numeric' == $polite_pagination_option) {
            global $wp_query;
            $big = 999999999; // need an unlikely integer
            // paginate_links() returns anchor markup, so kses rather than esc_html.
            // The arrows are decorative icons, not translatable strings.
            $polite_links = paginate_links(array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total' => $wp_query->max_num_pages,
                'prev_text' => '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                'next_text' => '<i class="fa fa-angle-right" aria-hidden="true"></i>',
            ));

            // paginate_links() returns null when there is one page or fewer, and
            // passing that to wp_kses_post() is deprecated on PHP 8.1+. It also
            // means there is no pagination worth wrapping in markup.
            if ( ! empty( $polite_links ) ) {
                echo "<div class='pagination'>";
                echo wp_kses_post( $polite_links );
                // Was an opening <div>, which left the .pagination wrapper unclosed.
                echo "</div>";
            }
        } elseif ('ajax' == $polite_pagination_option) {
            $page_number = get_query_var('paged');
            if ($page_number == 0) {
                $output_page = 2;
            } else {
                $output_page = $page_number + 1;
            }
            if(paginate_links()) {
            printf(
                '<div class="ajax-pagination text-center"><div class="show-more" data-number="%1$s"><i class="fa fa-refresh" aria-hidden="true"></i>%2$s</div><div id="free-temp-post"></div></div>',
                absint( $output_page ),
                esc_html__( 'View More', 'polite' )
            );
            }
        } else {
            return false;
        }
    }
endif;
add_action('polite_action_navigation', 'polite_posts_navigation', 10);