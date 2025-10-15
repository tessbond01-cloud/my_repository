<?php
/**
 * Template for displaying index page
 *
 * @package WordPress
 * @subpackage westface-child
 */

get_header(); ?>

<div class="container">
    <main id="main" class="site-main">
        <?php
        if (have_posts()) :
            while (have_posts()) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    </header>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            endwhile;
        else :
            ?>
            <article class="no-results">
                <header class="entry-header">
                    <h1 class="entry-title"><?php _e('Nothing Found', 'westface-child'); ?></h1>
                </header>

                <div class="entry-content">
                    <p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for.', 'westface-child'); ?></p>
                </div>
            </article>
            <?php
        endif;
        ?>
    </main>
</div>

<?php get_footer(); ?>
