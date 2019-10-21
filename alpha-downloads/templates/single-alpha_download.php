<?php get_header() ?>

  <?php the_post() ?>
  
  <article id="content" <?php post_class(array('wrapper', 'container')) ?>>
    <header class="content-header">
      <h1 class="title"><?php the_title() ?></h1>
      <?php
        /*
        * @Sidebar Content
        */
        get_sidebar('content-header'); ?>
    </header>
    <div class="content">blah
      <?php
        /*
        * @Sidebar Content
        */
        get_sidebar('meta'); ?>
      <?php the_content() ?>
      <?php wp_link_pages('before=<div class="page-link">' . __('Pages:', 'sandbox') . '&after=</div>') ?>
    </div>
  </article><!-- .post -->
  
<?php get_footer() ?>
