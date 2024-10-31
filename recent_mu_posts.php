<?php
   /*
   Plugin Name: Recent Posts Multi-sitewide
   Plugin URI: http://www.joker-enterprizes.com
   Description: A plugin that adds a shortcode for getting latest posts from all multi site blogs (Shortcode [recent_mu_posts howmany=10])
   Version: 1.0
   Author: Scott Baker
   Author URI: http://www.joker-enterprizes.com
   License: GPL2
   */
// [recent_mu_posts howmany=10]
if(!function_exists('recent_mu_posts')){
	function recent_mu_posts($args = ''){
		extract( shortcode_atts( array(
			'howmany' => 10,
		), $args ) );
		global $wpdb;
		global $table_prefix;
		 
		// get an array of the table names that our posts will be in
		// we do this by first getting all of our blog ids and then forming the name of the
		// table and putting it into an array
		$rows = $wpdb->get_results( "SELECT blog_id from $wpdb->blogs WHERE
		public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0';" );
		 
		if ( $rows ) :
			 
			$blogPostTableNames = array();
			foreach ( $rows as $row ) :
			 
				$blogPostTableNames[$row->blog_id] = $wpdb->get_blog_prefix( $row->blog_id ) . 'posts';
			 
			endforeach;
			# print_r($blogPostTableNames); # debugging code
			 
			// now we need to do a query to get all the posts from all our blogs
			// with limits applied
			if ( count( $blogPostTableNames ) > 0 ) :
				 
				$query = '';
				$i = 0;
				 
				foreach ( $blogPostTableNames as $blogId => $tableName ) :
					 
					if ( $i > 0 ) :
						$query.= ' UNION ';
					endif;
					 
					$query.= " (SELECT ID, post_date, $blogId as `blog_id` FROM $tableName WHERE post_status = 'publish' AND post_type = 'post')";
					$i++;
					 
				endforeach;
				 
				$query.= " ORDER BY post_date DESC LIMIT 0,$howmany;";
				# echo $query; # debugging code
				$rows = $wpdb->get_results( $query );
				 
				// now we need to get each of our posts into an array and return them
				if ( $rows ) :
					 
					$posts = array();
					foreach ( $rows as $row ) :
						$post = get_blog_post( $row->blog_id, $row->ID );
						$blog_id = $row->blog_id;
						switch_to_blog($blog_id);
							$listing = new WP_Query('p=' . $post->ID); if ($listing->have_posts()) : while ($listing->have_posts()) : $listing->the_post();
								if(strlen($post->post_content) > 500) :
									$content = '<p>';
									$content .= do_shortcode(substr(strip_tags($post->post_content), 0, 500));
									$content .= '...</p>';
								else:
									$content = do_shortcode($post->post_content);
								endif;
							endwhile; endif;
							$permalink = apply_filters('the_permalink', get_permalink( $post->ID));
						restore_current_blog();
						$blog=get_blog_details($blog_id);
						$posts[] = array(
							'blog_id' => $blog->blog_id,
							'blogname' => $blog->blogname,
							'siteurl' => $blog->siteurl,
							'the_permalink' => $permalink,
							'the_title' => get_the_title(),
							'date' => recent_mu_posts_get_the_time('F jS, Y', $post),
							'time' => apply_filters('the_time', recent_mu_posts_get_the_time("", $post), ""),
							'author_url' => get_the_author_meta('user_url', $post->post_author),
							'author_name' => get_the_author_meta('display_name', $post->post_author),
							'post_id' => $post->ID,
							'post_title' => $post->post_title,
							'content' => $content
						);
					endforeach;

					# echo "<pre>"; print_r($posts); echo "</pre>"; exit; # debugging code
					if(file_exists(STYLESHEETPATH . '/view/recent_mu_posts/view.php')){
						include(STYLESHEETPATH . '/view/recent_mu_posts/view.php');
					}else
					if(file_exists(WP_PLUGIN_DIR . '/recent_mu_posts/view.php' )){
						include( WP_PLUGIN_DIR . '/recent_mu_posts/view.php' );
					}else{
						$return = '';
					}
					return $return;
					 
				else:
				 
					return "Error: No Posts found";
				 
				endif;
			 
			else:
			 
				return "Error: Could not find blogs in the database";
			 
			endif;
			 
		else:
			 
			return "Error: Could not find blogs";
		 
		endif;
    }
}

add_shortcode( 'recent_mu_posts', 'recent_mu_posts' );

function recent_mu_posts_get_the_time( $d = '', $post ) {
	if ( '' == $d )
		$the_time = get_post_time(get_option('time_format'), false, $post, true);
	else
		$the_time = get_post_time($d, false, $post, true);
	return apply_filters('get_the_time', $the_time, $d, $post);
}
