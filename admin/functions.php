<?php
function recipressextend_get_page() {
  $requet_uri = str_replace('/wp-admin/', '', $_SERVER['REQUEST_URI']);
  
  return ($requet_uri) ? $requet_uri : 'index.php';
}

function recipressextend_print_link() {
  $recipressextend_options = get_option('recipressextend_options');
  
  $print_stub = ( isset($recipressextend_options['print_text']) && !empty($recipressextend_options['print_text']) ) ? $recipressextend_options['print_text'] : 'Print Recipe';
  
  $src = add_query_arg('yrecipressextend_print', '1', get_permalink());
  $content = '<p style="margin: 10px 0px;" id="recipressextend-print-link"><a src="' . $src . '" href="#">' . $print_stub . '</a></p>';
  
  return $content;
}

add_action('template_redirect', 'recipressextend_print', 5);
function recipressextend_print() {
  if (isset($_GET['yrecipressextend_print']) && $_GET['yrecipressextend_print'] == 1) {
    include( RECIPRESS_EXTEND_PLUGIN_DIR . 'print.php' );
    exit();
  }
}

add_action('admin_menu', 'recipressextend_option_menu');
function recipressextend_option_menu() {
  add_options_page('ReciPress Extend Options', 'ReciPress Extend', 'manage_options', 'recipressextend_options', 'recipressextend_options_page');
  add_filter('plugin_action_links', 'recipressextend_options_links', 10, 2);

  function recipressextend_options_links($links, $file) {
    if ($file != plugin_basename(__FILE__))
      return $links;
    $settings_link = '<a href="options-general.php?page=recipressextend_print">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
  }

}

add_action('admin_init', 'recipressextend_print_settings');
function recipressextend_print_settings() {
  register_setting('recipressextend_print', 'recipressextend_options');
}

function recipressextend_options_page() {
  $recipressextend_options = get_option('recipressextend_options');
  
  $recipressextend_options['print_text'] = isset($recipressextend_options['print_text']) ? $recipressextend_options['print_text'] : null;
  $recipressextend_options['ingredient_links'] = isset($recipressextend_options['ingredient_links']) ? $recipressextend_options['ingredient_links'] : null;
  $recipressextend_options['taxonomy_links'] = isset($recipressextend_options['taxonomy_links']) ? $recipressextend_options['taxonomy_links'] : null;
  ?>
    <div class="wrap">
      <div class="icon32" id="icon-options-general"></div>
      <h2>ReciPress Extend Options</h2>
      <form method="post" action="options.php">
        <?php settings_fields('recipressextend_print'); ?>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><label for="recipressextend_options[print_text]">Custom text instead of 'Print Recipe'</label></th>
              <td>
                <input type="text" class="regular-text code" value="<?php echo $recipressextend_options['print_text']; ?>" id="recipressextend_options[print_text]" name="recipressextend_options[print_text]">
                <p class="description">Type text</p>
              </td>
            </tr>
            <tr valign="top" <?php echo (recipressextend_recipress_version() == '1.9.5') ? 'style="display: none;"' : null; ?>>
              <th scope="row"><label>Ingredient Links</label></th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text">
                    <span>Ingredient Links</span>
                  </legend>
                  <p>
                    <label>
                      <input type="radio" value="1" name="recipressextend_options[ingredient_links]" <?php echo ( ($recipressextend_options['ingredient_links']=='1') || !isset($recipressextend_options['ingredient_links']) ) ? 'checked="checked"' : null;?>>
                      On
                    </label>
                    <br>
                    <label>
                      <input type="radio" value="0" name="recipressextend_options[ingredient_links]" <?php echo ( $recipressextend_options['ingredient_links']=='0' ) ? 'checked="checked"' : null;?>>
                      Off
                    </label>
                  </p>
                </fieldset>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label>Taxonomy Links</label></th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text">
                    <span>Taxonomy Links</span>
                  </legend>
                  <p>
                    <label>
                      <input type="radio" value="1" name="recipressextend_options[taxonomy_links]" <?php echo ( ($recipressextend_options['taxonomy_links']=='1') || !isset($recipressextend_options['taxonomy_links']) ) ? 'checked="checked"' : null;?>>
                      On
                    </label>
                    <br>
                    <label>
                      <input type="radio" value="0" name="recipressextend_options[taxonomy_links]" <?php echo ( $recipressextend_options['taxonomy_links']=='0' ) ? 'checked="checked"' : null;?>>
                      Off
                    </label>
                  </p>
                </fieldset>
              </td>
            </tr>
          </tbody>
        </table>
        <?php submit_button('Save Changes', 'primary', 'submit', 'true'); ?>
      </form>
    </div>
  <?php
}

/*
 * RSS
 */
// Add a Custom Field to WordPress RSS
if ( recipressextend_recipress_version_check() ) {
  add_filter('the_excerpt_rss', 'recipressextend_postrss');
  add_filter('the_content', 'recipressextend_postrss');
}

function recipressextend_postrss($content) {
  global $wp_query;
  
  $postid = $wp_query->post->ID;
  
  $recipe = get_the_recipe_extend( $postid, 'rss' );
  
  if (is_feed()) {
    if ($recipe !== '') {
      $content = $content . "<br /><br /><div>" . $recipe . "</div>";
    } else {
      $content = $content;
    }
  }

  return $content;
}

// Override the_recipe function
function the_recipe_extend($content) {
  return $content . get_the_recipe_extend( null, 'post' );
}

// Override recipress_autoadd function
if ( recipressextend_recipress_version_check() ) {
  remove_action('template_redirect', 'recipress_autoadd');
  add_action('template_redirect', 'recipressextend_autoadd');
}

function recipressextend_autoadd() {
  $autoadd = recipress_options('autoadd');
  
  if ( !isset($autoadd) || $autoadd == true || $autoadd == 'yes' ) {
    add_action('the_content', 'the_recipe_extend', 10);
  }
}

/*
 * 1.9.5
 */
if ( recipressextend_recipress_version() == '1.9.5' ) {
  
  // Override get_the_recipe function
  function get_the_recipe_extend( $postid, $output_type ) {
    global $post;
    
    $post = get_post( $postid );
    
    setup_postdata( $post );
    
    $recipress_output = true;
    if ($output_type == 'post') {
      $recipress_output = recipress_output();
    }
    
    // determine if post has a recipe
    if (has_recipress_recipe() && $recipress_output) {
      // create the array
      $recipe['before'] = '<div itemscope itemtype="http://data-vocabulary.org/Recipe" class="hrecipe ' . recipress_options('theme') . '" id="recipress_recipe">';
      $recipe['title'] = '<h2 itemprop="name" class="fn">' . recipress_recipe('title') . '</h2>';
      $recipe['photo'] = recipress_recipe('photo', array('class' => 'alignright photo recipress_thumb', 'itemprop' => 'photo'));
      $recipe['meta'] = '<p class="seo_only">' . _x('By', 'By Recipe Author', 'recipress') . ' <span itemprop="author" class="author">' . get_the_author() . '</span>
                ' . __('Published:', 'recipress') . ' <time datetime="' . get_the_date('Y-m-d') . '" itemprop="published" class="published updated">' . get_the_date('F j, Y') .
              '<span class="value-title" title="' . get_the_date('c') . '"></span></time></p>';
      
      // details
      $recipe['details_before'] = '<ul class="recipe-details">';
      if (recipress_recipe('yield'))
        $recipe['yield'] = '<li><b>' . __('Yield:', 'recipress') . '</b> <span itemprop="yield" class="yield">' . recipress_recipe('yield') . '</span></li>';
      if (recipress_recipe('cost') && recipress_options('cost_field') == true)
        $recipe['cost'] = '<li><b>' . __('Cost:', 'recipress') . '</b> <span class="cost">' . recipress_recipe('cost') . '</span></li>';
      if (recipress_recipe('prep_time') && recipress_recipe('cook_time'))
        $recipe['clear_items'] = '<li class="clear_items"></li>';
      if (recipress_recipe('prep_time'))
        $recipe['prep_time'] = '<li><b>' . __('Prep:', 'recipress') . '</b> <time datetime="' . recipress_recipe('prep_time', true) . '" itemprop="prepTime" class="preptime"><span class="value-title" title="' . recipress_recipe('prep_time', true) . '"></span>' . recipress_recipe('prep_time') . '</time></li>';
      if (recipress_recipe('cook_time'))
        $recipe['cook_time'] = '<li><b>' . __('Cook:', 'recipress') . '</b> <time datetime="' . recipress_recipe('cook_time', true) . '" itemprop="cookTime" class="cooktime"><span class="value-title" title="' . recipress_recipe('cook_time', true) . '"></span>' . recipress_recipe('cook_time') . '</time></li>';
      // if at least two of these three items exist: a && ( b || c ) || ( b && c )
      if (recipress_recipe('prep_time') && ( recipress_recipe('cook_time') || recipress_recipe('other_time') ) || ( recipress_recipe('cook_time') || recipress_recipe('other_time') ))
        $recipe['ready_time'] = '<li><b>' . __('Ready In:', 'recipress') . '</b> <time datetime="' . recipress_recipe('ready_time', true) . '" itemprop="totalTime" class="duration"><span class="value-title" title="' . recipress_recipe('ready_time', true) . '"></span>' . recipress_recipe('ready_time') . '</time></li>';
      $recipe['details_after'] = '</ul>';
      
      // summary
      $summary = recipress_recipe('summary');
      if (!$summary)
        $recipe['summary'] = '<p itemprop="summary" class="summary seo_only">' . recipress_gen_summary() . '</p>';
      else
        $recipe['summary'] = '<p itemprop="summary" class="summary">' . $summary . '</p>';
      
      // indredients
      $recipe['ingredients_title'] = '<h3>' . __('Ingredients', 'recipress') . '</h3>';
      $ingredient = recipress_recipe('ingredient');
      if (!empty($ingredient[0]['ingredient'])) {
        $recipe['ingredients'] = recipressextend_ingredients_list(recipress_recipe('ingredient'));
      } else {
        $recipe['ingredients'] = '<p style="margin: 0 0 0 1.57143rem;">No ingredient.</p>';
      }
      
      // instructions
      $recipe['instructions_title'] = '<h3>' . __('Instructions', 'recipress') . '</h3>';
      $instructions = recipress_recipe('instruction');
      
      if (!empty($instructions[0]['description'])) {
        $recipe['instructions'] = recipressextend_instructions_list($instructions);
      } else {
        $recipe['instructions'] = '<p style="margin: 0 0 0 1.57143rem;">No instruction.</p>';
      }
      
      // taxonomies
      $recipe['taxonomies_before'] = '<ul class="recipe-taxes">';
      $recipe['cuisine'] = recipressextend_recipe('cuisine', '<li><b>' . __('Cuisine', 'recipress') . ':</b> ', ', ', '</li>');
      $recipe['course'] = recipressextend_recipe('course', '<li><b>' . __('Course:', 'recipress') . '</b> ', ', ', '</li>');
      $recipe['skill_level'] = recipressextend_recipe('skill_level', '<li><b>' . __('Skill Level', 'recipress') . ':</b> ', ', ', '</li>');
      $recipe['taxonomies_after'] = '</ul>';
      if ($output_type == 'post') {
        $recipe['taxonomies_after'] = '<li>' . recipressextend_print_link() . '</li></ul>';
      }
      
      // close
      $recipe['credit'] = recipress_credit();
      $recipe['after'] = '</div><iframe name="printarea" id="printarea" src="" style="display: none;"></iframe>';
      
      // filter and return the recipe
      $recipe = apply_filters('the_recipe', $recipe);
      return implode('', $recipe);
    }
  }
  
  // Custom recipress_recipe function, use in get_the_recipe_extend function as Recipe Tax Links feature
  function recipressextend_recipe($field, $attr = null) {
    $output = false;
    $meta = get_post_meta(get_the_ID(), $field, true);
    
    $recipressextend_options = get_option('recipressextend_options');
    $recipressextend_options['taxonomy_links'] = isset($recipressextend_options['taxonomy_links']) ? $recipressextend_options['taxonomy_links'] : null;
    
    switch ( $field ) {
      // taxonomy terms: cuisine, course, skill_level
      case 'cuisine':
      case 'course':
      case 'skill_level':
        $output = get_the_term_list(get_the_ID(), $field, $attr);
        if ( $output && ( $recipressextend_options['taxonomy_links'] == '0' ) ) {
          $output = wp_get_object_terms(get_the_ID(), $field);
          $output = $attr . $output[0]->name . '</li>';
        }
        break;

      // plain output of field
      default:
        $output = $meta;
        break;
    } // end switch

    return $output;
  }
  
  function recipressextend_ingredients_list($ingredients) {
    $output = '<ul class="ingredients">';
    foreach ($ingredients as $ingredient) {
      $amount = $ingredient['amount'];
      $measurement = $ingredient['measurement'];
      $the_ingredient = $ingredient['ingredient'];
      $notes = $ingredient['notes'];

      if (!$ingredient['ingredient'])
        continue;

      $output .= '<li itemprop="ingredient" itemscope itemtype="http://data-vocabulary.org/RecipeIngredient" class="ingredient">';
      if (isset($amount) || isset($measurement))
        $output .= '<span itemprop="amount" class="amount">' . $amount . ' ' . $measurement . '</span> ';
      if (isset($the_ingredient))
        $term = get_term_by('name', $the_ingredient, 'ingredient');
      
      if (!empty($term) && recipress_options('link_ingredients') == true)
        $output .= '<span class="name"><a itemprop="name" href="' . get_term_link($term->slug, 'ingredient') . '">' . $the_ingredient . '</a></span> ';
      else
        $output .= '<span itemprop="name" class="name">' . $the_ingredient . '</span> ';
      
      if (isset($notes))
        $output .= '<i class="notes">' . $notes . '</i></li>';
    }
    $output .= '</ul>';

    return $output;
  }
  
  function recipressextend_instructions_list($instructions) {
    $output = '<ol itemprop="instructions" class="instructions">';
    foreach ($instructions as $instruction) {
      $size = recipress_options('instruction_image_size');
      $image = $instruction['image'] != '' ? wp_get_attachment_image($instruction['image'], $size, false, array('class' => 'align-' . $size)) : '';

      $output .= '<li>';
      if ($size == 'thumbnail' || $size == 'medium')
        $output .= $image;
      $output .= $instruction['description'];
      if ($size == 'large' || $size == 'full')
        $output .= '<br />' . $image;
      $output .= '</li>';
    }
    $output .= '</ol>';

    return $output;
  }
  
/*
 * 1.9.4
 */
} elseif ( recipressextend_recipress_version() == '1.9.4' ) {
  
  // Override get_the_recipe function
  function get_the_recipe_extend( $postid, $output_type ) {
    global $post;
    
    $post = get_post($postid);
    
    setup_postdata($post);
    
    $recipress_output = true;
    if ($output_type == 'post') {
      $recipress_output = recipress_output();
    }
    
    // determine if post has a recipe
    if (has_recipress_recipe() && $recipress_output) {
      // create the array
      $recipe['before'] = '<div itemscope itemtype="http://data-vocabulary.org/Recipe" class="hrecipe ' . recipress_theme() . '" id="recipress_recipe">';
      $recipe['title'] = '<h2 itemprop="name" class="fn">' . recipress_recipe('title') . '</h2>';
      $recipe['photo'] = recipress_recipe('photo', array( 'class' => 'alignright photo recipress_thumb', 'itemprop' => 'photo'));
      $recipe['meta'] = '<p class="seo_only">' . __('By', 'recipress') . ' <span itemprop="author" class="author">' . get_the_author() . '</span>
							' . __('Published:', 'recipress') . ' <time datetime="' . get_the_date('Y-m-d') . '" itemprop="published" class="published updated">' . get_the_date('F j, Y') . '<span class="value-title" title="' . get_the_date('c') . '"></span></time></p>';
      
      // details
      $recipe['details_before'] = '<ul class="recipe-details">';
      if (recipress_recipe('yield'))
        $recipe['yield'] = '<li><b>' . __('Yield:', 'recipress') . '</b> <span itemprop="yield" class="yield">' . recipress_recipe('yield') . '</span></li>';
      if (recipress_recipe('cost'))
        $recipe['cost'] = '<li><b>' . __('Cost:', 'recipress') . '</b> <span class="cost">' . recipress_recipe('cost') . '</span></li>';
      if (recipress_recipe('prep_time') && recipress_recipe('cook_time'))
        $recipe['clear_items'] = '<li class="clear_items"></li>';
      if (recipress_recipe('prep_time'))
        $recipe['prep_time'] = '<li><b>' . __('Prep:', 'recipress') . '</b> <time datetime="' . recipress_recipe('prep_time', 'iso') . '" itemprop="prepTime" class="preptime"><span class="value-title" title="' . recipress_recipe('prep_time', 'iso') . '"></span>' . recipress_recipe('prep_time', 'mins') . '</time></li>';
      if (recipress_recipe('cook_time'))
        $recipe['cook_time'] = '<li><b>' . __('Cook:', 'recipress') . '</b> <time datetime="' . recipress_recipe('cook_time', 'iso') . '" itemprop="cookTime" class="cooktime"><span class="value-title" title="' . recipress_recipe('cook_time', 'iso') . '"></span>' . recipress_recipe('cook_time', 'mins') . '</time></li>';
      if (recipress_recipe('prep_time') && recipress_recipe('cook_time'))
        $recipe['ready_time'] = '<li><b>' . __('Ready In:', 'recipress') . '</b> <time datetime="' . recipress_recipe('ready_time', 'iso') . '" itemprop="totalTime" class="duration"><span class="value-title" title="' . recipress_recipe('ready_time', 'iso') . '"></span>' . recipress_recipe('ready_time', 'mins') . '</time></li>';
      $recipe['details_after'] = '</ul>';
      
      // summary
      $summary = recipress_recipe('summary');
      if (!$summary)
        $recipe['summary'] = '<p itemprop="summary" class="summary seo_only">' . recipress_gen_summary() . '</p>';
      else
        $recipe['summary'] = '<p itemprop="summary" class="summary">' . $summary . '</p>';
      
      // indredients
      $recipe['ingredients_title'] = '<h3>' . __('Ingredients', 'recipress') . '</h3>';
      $recipe['ingredients'] = recipressextend_ingredients_list();
      
      // instructions
      if (recipressextend_instructions_list()) {
        $recipe['instructions_title'] = '<h3>' . __('Instructions', 'recipress') . '</h3>';
        $recipe['instructions'] = recipressextend_instructions_list();
      }
      
      // taxonomies
      $recipe['taxonomies_before'] = '<ul class="recipe-taxes">';
      $recipe['cuisine'] = recipressextend_recipe('cuisine', '<li><b>' . __('Cuisine', 'recipress') . ':</b> ', ', ', '</li>');
      $recipe['course'] = recipressextend_recipe('course', '<li><b>' . __('Course:', 'recipress') . '</b> ', ', ', '</li>');
      $recipe['skill_level'] = recipressextend_recipe('skill_level', '<li><b>' . __('Skill Level', 'recipress') . ':</b> ', ', ', '</li>');
      $recipe['taxonomies_after'] = '</ul>';
      if ($output_type == 'post') {
        $recipe['taxonomies_after'] = '<li>' . recipressextend_print_link() . '</li></ul>';
      }
      
      // close
      $recipe['credit'] = recipress_credit();
      $recipe['after'] = '</div><iframe name="printarea" id="printarea" src="" style="display: none;"></iframe>';
      
      // filter and return the recipe
      $recipe = apply_filters('the_recipe', $recipe);
      return implode('', $recipe);
    }
  }
  
  // Custom recipress_recipe function, use in get_the_recipe_extend function as Recipe Tax Links feature
  function recipressextend_recipe($field, $attr = null) {
    global $post;
    
    $meta = get_post_custom($post->ID);
    
    $recipressextend_options = get_option('recipressextend_options');
    $recipressextend_options['ingredient_links'] = isset($recipressextend_options['ingredient_links']) ? $recipressextend_options['ingredient_links'] : null;
    $recipressextend_options['taxonomy_links'] = isset($recipressextend_options['taxonomy_links']) ? $recipressextend_options['taxonomy_links'] : null;
    
    switch ($field) {
      // cuisine
      case 'cuisine':
        $cuisine = get_the_term_list($post->ID, 'cuisine', $attr);
        
        if ($cuisine && ( $recipressextend_options['taxonomy_links'] == '0' )) {
          $cuisine = wp_get_object_terms($post->ID, 'cuisine');
          $cuisine = $attr . $cuisine[0]->name . '</li>';
        }
        
        return $cuisine;
        break;
        
      // course
      case 'course':
        $course = get_the_term_list($post->ID, 'course', $attr);
        
        if ($course && ( $recipressextend_options['taxonomy_links'] == '0' )) {
          $course = wp_get_object_terms($post->ID, 'course');
          $course = $attr . $course[0]->name . '</li>';
        }
        
        return $course;
        break;
        
      // skill_level
      case 'skill_level':
        $skill_level = get_the_term_list($post->ID, 'skill_level', $attr);
        
        if ($skill_level && ( $recipressextend_options['taxonomy_links'] == '0' )) {
          $skill_level = wp_get_object_terms($post->ID, 'skill_level');
          $skill_level = $attr . $skill_level[0]->name . '</li>';
        }
        
        return $skill_level;
        break;
        
      default:
        return $meta[$field][0];
    } // end switch
  }
  
  // recipressextend_ingredients_list
  function recipressextend_ingredients_list() {
    $recipressextend_options = get_option('recipressextend_options');
    $recipressextend_options['ingredient_links'] = isset($recipressextend_options['ingredient_links']) ? $recipressextend_options['ingredient_links'] : null;

    $ingredients = recipress_recipe('ingredients');

    $output = '<ul class="ingredients">';
    foreach ($ingredients as $ingredient) {
      $amount = $ingredient['amount'];
      $measurement = $ingredient['measurement'];
      $the_ingredient = $ingredient['ingredient'];
      $notes = $ingredient['notes'];

      if (!$ingredient['ingredient']) {
        $output = '<p style="margin: 0 0 0 1.57143rem;">No ingredient.</p>';
        continue;
      }

      $output .= '<li itemprop="ingredient" itemscope itemtype="http://data-vocabulary.org/RecipeIngredient" class="ingredient">';
      if (isset($amount) || isset($measurement))
        $output .= '<span itemprop="amount" class="amount">' . $amount . ' ' . $measurement . '</span> ';
      if (isset($the_ingredient))
        $term = get_term_by('name', $the_ingredient, 'ingredient');

      if ((!empty($term) ) && ( ($recipressextend_options['ingredient_links'] == '1') || ($recipressextend_options['ingredient_links'] == null) ))
        $output .= '<span class="name"><a itemprop="name" href="' . get_term_link($term->slug, 'ingredient') . '">' . $the_ingredient . '</a></span> ';
      else
        $output .= '<span class="name">' . $the_ingredient . '</span> ';
      
      if (isset($notes))
        $output .= '<i class="notes">' . $notes . '</i></li>';
    }
    $output .= '</ul>';

    return $output;
  }

  // recipressextend_instructions_list
  function recipressextend_instructions_list() {
    $output = '<p style="margin: 0 0 0 1.57143rem;">No instruction.</p>';

    $instructions = recipress_recipe('instructions');

    if (!empty($instructions[0]['description'])) {
      $output = '<ol itemprop="instructions" class="instructions">';
      foreach ($instructions as $instruction) {
        $size = recipress_options('instruction_image_size');
        if (!isset($size))
          $size = 'large';
        $image = $instruction['image'] != '' ? wp_get_attachment_image($instruction['image'], $size, false, array('class' => 'align-' . $size)) : '';

        $output .= '<li>';
        if ($size == 'thumbnail' || $size == 'medium')
          $output .= $image;
        $output .= $instruction['description'];
        if ($size == 'large' || $size == 'full')
          $output .= '<br />' . $image;
        $output .= '</li>';
      }
      $output .= '</ol>';
    }

    return $output;
  }
  
}
