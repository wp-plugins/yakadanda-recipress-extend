<!DOCTYPE html>
<html>
<head>
  <title><?php
      global $wpdb, $page;
      wp_title( '|', true, 'right' );
      bloginfo( 'name' );
      $site_description = get_bloginfo( 'description', 'display' );
    ?></title>
  <link rel="stylesheet" id="print-css"  href='<?php echo RECIPRESS_EXTEND_PLUGIN_URL . '/css/style.css'; ?>' type="text/css" media="all">
  <link rel="stylesheet" id="front-css"  href='<?php echo plugins_url( '/recipress/css/recipress.wp.css' ); ?>' type="text/css" media="all">
</head>
<body>
  <div id="content">
    <?php if ( have_posts() ) while ( have_posts() ) : the_post();?>
      <div id="article">
        <h1 class="article-title"><?php the_title(); ?></h1>
        <?php
          if ( has_post_thumbnail() ) the_post_thumbnail();
          if ( function_exists( 'woo_post_meta' ) ) woo_post_meta();
        ?>
        <div class="article-content"><?php the_content(); ?></div>
        <?php echo get_the_recipe_extend( get_the_ID(), 'print_preview' ); ?>
      </div>
    <?php endwhile; ?>
  </div>
  <div id="footer">Copyright &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></div>
</body>
</html>
