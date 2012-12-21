<?php
// Add a Custom Field to WordPress RSS
function recipressextend_postrss($content) {
  global $wp_query;
  $postid = $wp_query->post->ID;
  
  $recipe = get_the_recipe_extend( $postid , 'rss' );
  
  if ( is_feed() ) {
    if ($recipe !== '') {
      $content = $content . "<br /><br /><div>" . $recipe . "</div>";
    } else {
      $content = $content;
    }
  }
  return $content;
}
add_filter( 'the_excerpt_rss', 'recipressextend_postrss' );
add_filter( 'the_content', 'recipressextend_postrss' );

function get_the_recipe_extend( $postid=null , $output_type) {
  global $post;
  
  $post = get_post( $postid );
  
  setup_postdata($post);
  
  $recipress_output = true;
  if ( $output_type == 'post' ) {
    $recipress_output = recipress_output();
  }
  
	// determine if post has a recipe
	if(has_recipress_recipe() && $recipress_output) {
		// create the array
		$recipe['before'] = '<div class="hrecipe '.recipress_theme().'" id="recipress_recipe">';
		$recipe['title'] = '<h2 class="fn">'.recipress_recipe('title').'</h2>';
		$recipe['photo'] = recipress_recipe('photo', 'class=alignright photo recipress_thumb');
		$recipe['meta'] = '<p class="seo_only">'.__('By', 'recipress').' <span class="author">'.get_the_author().'</span>
							'.__('Published:', 'recipress').' <span class="published updated">'.get_the_date('F j, Y').'<span class="value-title" title="'.get_the_date('c').'"></span></span></p>';
							
		// details
		$recipe['details_before'] = '<ul class="recipe-details">';
		if(recipress_recipe('yield'))
			$recipe['yield'] = '<li><b>'.__('Yield:', 'recipress').'</b> <span class="yield">'.recipress_recipe('yield').'</span></li>';
		if(recipress_recipe('cost'))
			$recipe['cost'] = '<li><b>'.__('Cost:', 'recipress').'</b> <span class="cost">'.recipress_recipe('cost').'</span></li>';
		if(recipress_recipe('prep_time') && recipress_recipe('cook_time'))
			$recipe['clear_items'] = '<li class="clear_items"></li>';
		if(recipress_recipe('prep_time'))
			$recipe['prep_time'] = '<li><b>'.__('Prep:', 'recipress').'</b> <span class="preptime"><span class="value-title" title="'.recipress_recipe('prep_time', 'iso').'"></span>'.recipress_recipe('prep_time','mins').'</span></li>';
		if(recipress_recipe('cook_time'))
			$recipe['cook_time'] = '<li><b>'.__('Cook:', 'recipress').'</b> <span class="cooktime"><span class="value-title" title="'.recipress_recipe('cook_time','iso').'"></span>'.recipress_recipe('cook_time','mins').'</span></li>';
		if(recipress_recipe('prep_time') && recipress_recipe('cook_time'))
			$recipe['ready_time'] ='<li><b>'.__('Ready In:', 'recipress').'</b> <span class="duration"><span class="value-title" title="'.recipress_recipe('ready_time','iso').'"></span>'.recipress_recipe('ready_time','mins').'</span></li>';
		$recipe['details_after'] = '</ul>';
		
		// summary
		$summary = recipress_recipe('summary');
		if(!$summary)
			$recipe['summary'] = '<p class="summary seo_only">'.recipress_gen_summary().'</p>';
		else
			$recipe['summary'] = '<p class="summary">'.$summary.'</p>';
		
		// indredients
		$recipe['ingredients_title'] = '<h3>'.__('Ingredients', 'recipress').'</h3>';
		$recipe['ingredients'] = recipressextend_ingredients_list();
		
		// instructions
    if ( recipressextend_instructions_list() ) {
      $recipe['instructions_title'] = '<h3>'.__('Instructions', 'recipress').'</h3>';
      $recipe['instructions'] = recipressextend_instructions_list();
    }
    
		// taxonomies
		$recipe['taxonomies_before'] = '<ul class="recipe-taxes">';
		$recipe['cuisine'] = recipressextend_recipe('cuisine', '<li><b>'.__('Cuisine', 'recipress').':</b> ', ', ', '</li>');
		$recipe['course'] = recipressextend_recipe('course', '<li><b>'.__('Course:', 'recipress').'</b> ', ', ', '</li>');
		$recipe['skill_level'] = recipressextend_recipe('skill_level', '<li><b>'.__('Skill Level', 'recipress').':</b> ', ', ', '</li>');
    $recipe['taxonomies_after'] = '</ul>';
    if ($output_type == 'post') {
      $recipe['taxonomies_after'] = '<li>' . recipressextend_print_link() . '</li></ul>';
    }
		
		// close
		$recipe['credit'] = recipress_credit();
		$recipe['after'] = '</div>';
	
	// filter and return the recipe
	$recipe = apply_filters('the_recipe',$recipe);
	return implode( '', $recipe );
	}
}

// function for outputting recipe items
// ----------------------------------------------------
function recipressextend_recipe($field, $attr = null) {
	global $post;
	$meta = get_post_custom($post->ID);
  $recipressextend_options = get_option( 'recipressextend_options' );
	
	switch($field) {
		// cuisine
		case 'cuisine':
			$cuisine = get_the_term_list( $post->ID, 'cuisine', $attr);
      
      if ( $cuisine && ( $recipressextend_options['ingredient_tax_links'] == 'off' ) ) {
        $cuisine = wp_get_object_terms($post->ID, 'cuisine');
        $cuisine = $attr . $cuisine[0]->name . '</li>';
      }
      
			return $cuisine;
		break;
		
		// course
		case 'course':
			$course = get_the_term_list( $post->ID, 'course', $attr);
      
      if ( $course && ( $recipressextend_options['ingredient_tax_links'] == 'off' ) ) {
        $course = wp_get_object_terms($post->ID, 'course');
        $course = $attr . $course[0]->name . '</li>';
      }
      
			return $course;
		break;
		
		// skill_level
		case 'skill_level':
			$skill_level = get_the_term_list( $post->ID, 'skill_level', $attr);
      
      if ( $skill_level && ( $recipressextend_options['ingredient_tax_links'] == 'off' ) ) {
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
  $recipressextend_options = get_option( 'recipressextend_options' );
	$ingredients = recipress_recipe('ingredients');
	$output = '<ul class="ingredients">';
	foreach($ingredients as $ingredient) {
		$amount = $ingredient['amount'];
		$measurement = $ingredient['measurement'];
		$the_ingredient = $ingredient['ingredient'];
		$notes = $ingredient['notes'];
		
		if(!$ingredient['ingredient']) continue;
		
		$output .= '<li class="ingredient">';
		if (isset($amount) || isset($measurement)) 
			$output .= '<span class="amount">'.$amount.' '.$measurement.'</span> ';
		if (isset($the_ingredient))
			$term = get_term_by('name', $the_ingredient, 'ingredient');
			$output .= '<span class="name">';
      
			if ( ( !empty($term) ) && ( ($recipressextend_options['ingredient_tax_links'] == 'on') || empty($recipressextend_options['ingredient_tax_links']) ) )
        $output .= '<a href="'.get_term_link($term->slug, 'ingredient').'">';
      
			$output .= $the_ingredient;
      
			if ( ( !empty($term) ) && ( ($recipressextend_options['ingredient_tax_links'] == 'on') || empty($recipressextend_options['ingredient_tax_links']) ) )
        $output .= '</a>';
      
			$output .= '</span> ';
		if (isset($notes)) 
			$output .= '<i class="notes">'.$notes.'</i></li>';
	}
	$output .= '</ul>';
	
	return $output;
}

// recipressextend_instructions_list
function recipressextend_instructions_list() {
  $output = null;
	$instructions = recipress_recipe('instructions');
  if (!empty($instructions[0]['description'])) {
    $output = '<ol class="instructions">';
    foreach($instructions as $instruction) {
      $size = recipress_options('instruction_image_size');
      if (!isset($size)) $size = 'large';
      $image = $instruction['image'] != '' ? wp_get_attachment_image($instruction['image'], $size, false, array('class' => 'align-'.$size)) : '';

      $output .= '<li>';
      if ($size == 'thumbnail' || $size == 'medium') 
        $output .= $image;
      $output .= $instruction['description'];
      if ($size == 'large' || $size == 'full') 
        $output .= '<br />'.$image;
      $output .= '</li>';
    }
    $output .= '</ol>';
  }
	
	return $output;
}

function the_recipe_extend($content) {
	return $content.get_the_recipe_extend( null, 'post' );
}

function recipressextend_autoadd() {
	$autoadd = recipress_options('autoadd');
	if ( !isset($autoadd) || $autoadd == 'yes' ) {
		add_action('the_content', 'the_recipe_extend', 10);
	}
}
remove_action('template_redirect', 'recipress_autoadd');
add_action('template_redirect', 'recipressextend_autoadd');
