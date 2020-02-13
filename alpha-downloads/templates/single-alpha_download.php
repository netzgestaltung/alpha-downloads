<?php get_header() ?>

  <?php the_post() ?>
  
  <?php
    $customPostsMeta = get_post_custom();
    $post_id = get_the_ID();
    $file_url = $customPostsMeta['_alpha_file_url'][0];
    $file_size = $customPostsMeta['_alpha_file_size'][0];
    $description = $customPostsMeta['_download-description'][0];
    $exercise = $customPostsMeta['_download-challenge'][0];
    $author = $customPostsMeta['_download-author'][0];
    $license = $customPostsMeta['_download-license'][0];
    $lern_steps_tax = get_taxonomy('lern_stufe');
    $download_categories = get_taxonomy('ddownload_category');
    $download_tags = get_taxonomy('ddownload_tag');
    $materials_tax = get_taxonomy('unterlage_uebung');
	  $lern_steps_terms = get_the_terms(get_the_ID(), $lern_steps_tax->name);
	  $lern_steps = array();
    if ( !empty($lern_steps_terms) && !is_wp_error($lern_steps_terms) ) {
      foreach ( $lern_steps_terms as $lern_steps_term ) {
        $lern_steps[] = $lern_steps_term->name;
      }
    }
	  $categories_terms = get_the_terms(get_the_ID(), $download_categories->name);
	  $categories = array();
    if ( !empty($categories_terms) && !is_wp_error($categories_terms) ) {
      foreach ( $categories_terms as $categories_term ) {
        $categories[] = $categories_term->name;
      }
    }
	  $tags_terms = get_the_terms(get_the_ID(), $download_tags->name);
	  $tags = array();
    if ( !empty($tags_terms) && !is_wp_error($tags_terms) ) {
      foreach ( $tags_terms as $tags_term ) {
        $tags[] = $tags_term->name;
      }
    }
	  $materials_terms = get_the_terms(get_the_ID(), $materials_tax->name);
	  $materials = array();
    if ( !empty($materials_terms) && !is_wp_error($materials_terms) ) {
      foreach ( $materials_terms as $materials_term ) {
        $materials[] = $materials_term->name;
      }
    }
    // $moreLinkURL = $customPostsMeta['_more-link-url'][0];
    // echo var_dump($lern_steps);
  ?>

  <article id="content" <?php post_class(array('wrapper', 'container')) ?>>
    <header class="content-header">
      <h1 class="title"><?php the_title(); ?></h1>
      <?php
        /*
        * @Sidebar Content
        */
        get_sidebar('content-header'); ?>
    </header>
    <div class="content">
      <aside class="sidebar region meta wrapper container">
        <div class="inner">
        <?php if ( !empty($lern_steps) ) { ?>
          <div class="<?php echo $lern_steps_tax->name; ?>"><?php echo implode(', ', $lern_steps)?></div>
        <?php } ?>
          <a class="download" href="<?php echo alpha_download_link($post_id); ?>"><?php echo __('Download', 'sandbox'); ?> (<?php echo strtoupper(alpha_get_file_ext($file_url)); ?> <?php echo size_format($customPostsMeta['_alpha_file_size'][0], 1); ?>)</a>
        <?php if ( !empty($author) ) { ?>
          <h3 class="author"><?php echo $author; ?><?php if ( !empty($license) ) { ?> | <?php echo $license; ?><?php } ?></h3>
        <?php } ?>
        <?php if ( function_exists('wpcr_avg_rating') ) { ?>
          <?php echo wpcr_avg_rating(array('title' => 'test')); ?>
        <?php } ?>
        </div>
      </aside>
      <?php the_content() ?>
    <?php if ( !empty($description) ) { ?>
      <h4><?php echo __('Beschreibung', 'sandbox'); ?></h4>
      <?php echo apply_filters('the_content', $description); ?>
    <?php } ?>
    <?php if ( !empty($exercise) ) { ?>
      <h4><?php echo __('Aufgabe', 'sandbox'); ?></h4>
      <?php echo apply_filters('the_content', $exercise); ?>
    <?php } ?>
      <dl>
      <?php if ( !empty($categories) ) { ?>
        <dt><?php echo $download_categories->label; ?></dt>
        <dd><?php echo implode(', ', $categories)?></dd>
      <?php } ?>
      <?php if ( !empty($tags) ) { ?>
        <dt><?php echo $download_tags->label; ?></dt>
        <dd><?php echo implode(', ', $tags)?></dd>
      <?php } ?>
      <?php if ( !empty($materials) ) { ?>
        <dt><?php echo $materials_tax->label; ?></dt>
        <dd><?php echo implode(', ', $materials)?></dd>
      <?php } ?>
      </dl>
    </div>
  <?php  // If comments are open or we have at least one comment, load up the comment template. ?>
  <?php  if ( comments_open() || get_comments_number() ) { ?>
    <?php comments_template(); ?>
  <?php  } ?>
  </article><!-- .post -->
  
<?php get_footer() ?>
