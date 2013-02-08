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

// Override get_the_recipe function
function get_the_recipe_extend( $postid = null , $output_type ) {
  global $post;
  
  $post = get_post( $postid );
  
  setup_postdata($post);
  
  $recipress_output = true;
  if ( $output_type == 'post' ) {
    $recipress_output = recipress_output();
  }
  
	// determine if post has a recipe
	if( has_recipress_recipe() && $recipress_output ) {
		// create the array
		$recipe['before'] = '<div class="hrecipe ' . recipress_options( 'theme' ) . '" id="recipress_recipe">';
		$recipe['title'] = '<h2 class="fn">' . recipress_recipe( 'title' ) . '</h2>';
		$recipe['photo'] = recipress_recipe( 'photo', array( 'class' => 'alignright photo recipress_thumb' ) );
		$recipe['meta'] = '<p class="seo_only">' . _x( 'By', 'By Recipe Author', 'recipress' ) . ' <span class="author">' . get_the_author() . '</span>
							' . __( 'Published:', 'recipress' ) . ' <span class="published updated">' . get_the_date( 'F j, Y' ) . 
							'<span class="value-title" title="' . get_the_date( 'c' ) . '"></span></span></p>';
							
		// details
		$recipe['details_before'] = '<ul class="recipe-details">';
		if( recipress_recipe( 'yield' ) )
			$recipe['yield'] = '<li><b>' . __( 'Yield:', 'recipress' ) . '</b> <span class="yield">' . recipress_recipe( 'yield' ) . '</span></li>';
		if( recipress_recipe( 'cost' ) && recipress_options( 'cost_field' ) == true )
			$recipe['cost'] = '<li><b>' . __( 'Cost:', 'recipress' ) . '</b> <span class="cost">' . recipress_recipe( 'cost' ) . '</span></li>';
		if( recipress_recipe( 'prep_time' ) && recipress_recipe( 'cook_time' ) )
			$recipe['clear_items'] = '<li class="clear_items"></li>';
		if( recipress_recipe( 'prep_time' ) )
			$recipe['prep_time'] = '<li><b>' . __( 'Prep:', 'recipress' ) . '</b> <span class="preptime"><span class="value-title" title="' . recipress_recipe( 'prep_time', true ) . '"></span>' . recipress_recipe( 'prep_time' ) . '</span></li>';
		if( recipress_recipe( 'cook_time' ) )
			$recipe['cook_time'] = '<li><b>' . __( 'Cook:', 'recipress' ) . '</b> <span class="cooktime"><span class="value-title" title="' . recipress_recipe( 'cook_time', true ) . '"></span>' . recipress_recipe( 'cook_time' ) . '</span></li>';
		// if at least two of these three items exist: a && ( b || c ) || ( b && c )
		if( recipress_recipe( 'prep_time' ) && ( recipress_recipe( 'cook_time' ) || recipress_recipe( 'other_time' ) ) || ( recipress_recipe( 'cook_time' ) || recipress_recipe( 'other_time' ) ) )
			$recipe['ready_time'] ='<li><b>' . __( 'Ready In:', 'recipress' ) . '</b> <span class="duration"><span class="value-title" title="' . recipress_recipe( 'ready_time',true ) . '"></span>' . recipress_recipe( 'ready_time' ) . '</span></li>';
		$recipe['details_after'] = '</ul>';
		
		// summary
		$summary = recipress_recipe( 'summary' );
		if( ! $summary )
			$recipe['summary'] = '<p class="summary seo_only">' . recipress_gen_summary() . '</p>';
		else
			$recipe['summary'] = '<p class="summary">' . $summary . '</p>';
		
		// indredients
		$recipe['ingredients_title'] = '<h3>' . __( 'Ingredients', 'recipress' ) . '</h3>';
    $ingredient = recipress_recipe( 'ingredient' );
    if (!empty($ingredient[0]['ingredient'])) {
      $recipe['ingredients'] = recipress_ingredients_list( recipress_recipe( 'ingredient' ) );
    } else {
      $recipe['ingredients'] = '<p style="margin: 0 0 0 1.57143rem;">No ingredient.</p>';
    }
    
		// instructions
    $recipe['instructions_title'] = '<h3>' . __( 'Instructions', 'recipress' ) . '</h3>';
    $instructions = recipress_recipe( 'instruction' );
    if (!empty($instructions[0]['description'])) {
      $recipe['instructions'] = recipress_instructions_list( $instructions );
    } else {
      $recipe['instructions'] = '<p style="margin: 0 0 0 1.57143rem;">No instruction.</p>';
    }
					
		// taxonomies
		$recipe['taxonomies_before'] = '<ul class="recipe-taxes">';
		$recipe['cuisine'] = recipressextend_recipe( 'cuisine', '<li><b>' . __( 'Cuisine', 'recipress' ) . ':</b> ', ', ', '</li>' );
		$recipe['course'] = recipressextend_recipe( 'course', '<li><b>' . __( 'Course:', 'recipress' ) . '</b> ', ', ', '</li>' );
		$recipe['skill_level'] = recipressextend_recipe( 'skill_level', '<li><b>' . __( 'Skill Level', 'recipress' ) . ':</b> ', ', ', '</li>' );
    $recipe['taxonomies_after'] = '</ul>';
    if ($output_type == 'post') {
      $recipe['taxonomies_after'] = '<li>' . recipressextend_print_link() . '</li></ul>';
    }
    
		// close
		$recipe['credit'] = recipress_credit();
		$recipe['after'] = '</div>';
	
	// filter and return the recipe
	$recipe = apply_filters( 'the_recipe',$recipe);
	return implode( '', $recipe );
	}
}

// Custom recipress_recipe function, use in get_the_recipe_extend function as Recipe Tax Links feature
function recipressextend_recipe( $field, $attr = null ) {
	$output = false;
	$meta = get_post_meta( get_the_ID(), $field, true );
  $recipressextend_options = get_option( 'recipressextend_options' );
	
	switch( $field ) {
		// taxonomy terms: cuisine, course, skill_level
		case 'cuisine':
		case 'course':
		case 'skill_level':
			$output = get_the_term_list( get_the_ID(), $field, $attr );
      if ( $output && ( $recipressextend_options['tax_links'] == 'off' ) ) {
        $output = wp_get_object_terms( get_the_ID(), $field );
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

// Override the_recipe function
function the_recipe_extend( $content ) {
	return $content . get_the_recipe_extend( null, 'post' );
}

// Override recipress_autoadd function
remove_action('template_redirect', 'recipress_autoadd');
add_action('template_redirect', 'recipressextend_autoadd');
function recipressextend_autoadd() {
  $autoadd = recipress_options('autoadd');
  if ( !isset($autoadd) || $autoadd == true ) {
		add_action( 'the_content', 'the_recipe_extend', 10 );
	}
}
