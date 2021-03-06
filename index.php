<?php
/*
Plugin Name: Movie DB
Plugin URI: http://deviserweb.com
Description: A plugin to create and maintain movie database
Version: 1.0
Author: DeviserWeb
Author URI: http://deviserweb.com
License: Single Use
*/

if($_SERVER['SCRIPT_FILENAME'] == __FILE__)
{
  header('Location: /');
  exit;
}

define( 'MDB_POST_TYPE', 'post' );

/**
 * Do things that are needed to be done on plugin init.
 */
function mdb_init(){
	add_image_size( 'poster-thumbnail', 160, 237, false );
}
add_action( 'init', 'mdb_init' );

/**
 * Test any data with this function.
 * 
 * @param mixed $data The data to check
 * @param bool $var_dump Wheather to var_dump() the data or just use print_r()
 */
function mdb_debug( $data, $var_dump = false ){
	if( !WP_DEBUG ) return;
	
	echo "<pre>";
	
	if( $var_dump )
		var_dump( $data );
	else
		print_r( $data );
		
	echo "</pre>";
}

/**
 * Creates database on plugin activation
 */
function mdb_activated(){
	global $wpdb;
	
	$wpdb->query("CREATE TABLE `{$wpdb->prefix}movie_cast_n_crew` (
		  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `movie` int(11) unsigned NOT NULL,
		  `profile` int(11) unsigned NOT NULL,
		  `role` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `task` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `type` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `featured` tinyint(1) DEFAULT '0',
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
}
register_activation_hook(__FILE__, 'mdb_activated');

/**
 * Add featured image support to movies and profiles
 */
function mdb_support_featured_image(){
	add_theme_support( 'post_thumbnails', array( MDB_POST_TYPE, 'profile' ) );
	add_image_size( 'small-thumb', 50, 50, true );
}
add_action( 'after_setup_theme', 'mdb_support_featured_image' );


/**
 * Enqueues javascript and css files in admin area
 */
function mdb_admin_enqueue(){

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 
		'jquery-add-input-area', 
		plugins_url( 'js/jquery.add-input-area.js', __FILE__ ), 
		array( 'jquery' ), 
		'4.7.1' );
	wp_enqueue_script( 
		'movie-db', 
		plugins_url( 'js/movie-db.js', __FILE__ ), 
		array( 'jquery', 'jquery-add-input-area' ), 
		'1.0' );
	wp_localize_script( 
		'movie-db', 
		'mdb', 
		array( 
			'defaultposter' => plugins_url( 'img/defaultposter.jpg', __FILE__ ),
			'mistryman'		=> plugins_url( 'img/mistryman.jpg', __FILE__ ),
			'ajax_url'		=> admin_url( '/admin-ajax.php' ) ) );

	wp_enqueue_style( 'movie-db', plugins_url( 'css/movie-db.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'mdb_admin_enqueue' );


/**
 * Enqueues js and css for front end
 */
function mdb_enqueue(){
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'slick', plugins_url( 'js/slick.min.js', __FILE__ ), array('jquery'), '1.3.15' );
	wp_enqueue_script( 'fancybox', plugins_url( 'js/jquery.fancybox.pack.js', __FILE__ ), array('jquery'), '2.1.5' );
	wp_enqueue_script( 'collapse', plugins_url( 'js/jquery.collapse.js', __FILE__ ), array('jquery') );
	wp_enqueue_script( 'readmore', plugins_url( 'js/readmore.min.js', __FILE__ ), array('jquery') );
	// wp_enqueue_script( 'owl-carousel', plugins_url( 'js/owl.carousel.min.js', __FILE__ ), array('jquery'), '1.3.3' );

	wp_enqueue_style( 'slick', plugins_url( 'css/slick.css', __FILE__ ) );
	wp_enqueue_style( 'fancybox', plugins_url( 'css/jquery.fancybox.css', __FILE__ ) );
	// wp_enqueue_style( 'owl-carousel', plugins_url( 'css/owl.carousel.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'mdb_enqueue' );

/**
 * Add additional query option to wp query.
 * 
 * Make it possible to query posts by title similarity.
 */
function mdb_add_title_query_filter( $where, &$wp_query ) {
	global $wpdb;
	if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
		$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( $post_title_like ) . '%\'';
	}
	return $where;
}
add_filter( 'posts_where', 'mdb_add_title_query_filter', 10, 2 );


/**
 * Register any new post type or taxonomies.
 */
function mdb_register_things() {
	
	$labels = array(
		'name'                       => _x( 'Generes', 'taxonomy general name', 'mdb' ),
		'singular_name'              => _x( 'Genere', 'taxonomy singular name', 'mdb' ),
		'search_items'               => __( 'Search generes', 'mdb' ),
		'popular_items'              => __( 'Popular generes', 'mdb' ),
		'all_items'                  => __( 'All generes', 'mdb' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Genere', 'mdb' ),
		'update_item'                => __( 'Update Genere', 'mdb' ),
		'add_new_item'               => __( 'Add New Genere', 'mdb' ),
		'new_item_name'              => __( 'New Genere Name' ),
		'separate_items_with_commas' => __( 'Separate generes with commas', 'mdb' ),
		'add_or_remove_items'        => __( 'Add or remove generes', 'mdb' ),
		'choose_from_most_used'      => __( 'Choose from the most used generes', 'mdb' ),
		'not_found'                  => __( 'No generes found.', 'mdb' ),
		'menu_name'                  => __( 'Generes', 'mdb' ),
	);

	$args = array(
		'hierarchical'         		 => false,
		'labels'               		 => $labels,
		'show_ui'              		 => true,
		'show_admin_column'    		 => true,
		'update_count_callback'		 => '_update_post_term_count',
		'query_var'            		 => true,
		'rewrite'              		 => array( 'slug' => 'genere' ),
	);

	register_taxonomy( 'genere', MDB_POST_TYPE, $args );

	$labels = array(
		'name'                       => _x( 'Countries', 'taxonomy general name', 'mdb' ),
		'singular_name'              => _x( 'Country', 'taxonomy singular name', 'mdb' ),
		'search_items'               => __( 'Search production companies', 'mdb' ),
		'popular_items'              => __( 'Popular production companies', 'mdb' ),
		'all_items'                  => __( 'All production companies', 'mdb' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Country', 'mdb' ),
		'update_item'                => __( 'Update Country', 'mdb' ),
		'add_new_item'               => __( 'Add New Country', 'mdb' ),
		'new_item_name'              => __( 'New Country Name' ),
		'separate_items_with_commas' => __( 'Separate production companies with commas', 'mdb' ),
		'add_or_remove_items'        => __( 'Add or remove production companies', 'mdb' ),
		'choose_from_most_used'      => __( 'Choose from the most used production companies', 'mdb' ),
		'not_found'                  => __( 'No production companies found.', 'mdb' ),
		'menu_name'                  => __( 'Countries', 'mdb' ),
	);

	$args = array(
		'hierarchical'          	 => false,
		'labels'                	 => $labels,
		'show_ui'               	 => true,
		'show_admin_column'     	 => true,
		'update_count_callback' 	 => '_update_post_term_count',
		'query_var'             	 => true,
		'rewrite'               	 => array( 'slug' => 'country' ),
	);

	register_taxonomy( 'country', MDB_POST_TYPE, $args );

	$labels = array(
		'name'                       => _x( 'Languages', 'taxonomy general name', 'mdb' ),
		'singular_name'              => _x( 'Language', 'taxonomy singular name', 'mdb' ),
		'search_items'               => __( 'Search languages', 'mdb' ),
		'popular_items'              => __( 'Popular languages', 'mdb' ),
		'all_items'                  => __( 'All languages', 'mdb' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Language', 'mdb' ),
		'update_item'                => __( 'Update Language', 'mdb' ),
		'add_new_item'               => __( 'Add New Language', 'mdb' ),
		'new_item_name'              => __( 'New Language Name' ),
		'separate_items_with_commas' => __( 'Separate languages with commas', 'mdb' ),
		'add_or_remove_items'        => __( 'Add or remove languages', 'mdb' ),
		'choose_from_most_used'      => __( 'Choose from the most used languages', 'mdb' ),
		'not_found'                  => __( 'No languages found.', 'mdb' ),
		'menu_name'                  => __( 'Languages', 'mdb' ),
	);

	$args = array(
		'hierarchical'          	 => false,
		'labels'                	 => $labels,
		'show_ui'               	 => true,
		'show_admin_column'     	 => true,
		'update_count_callback' 	 => '_update_post_term_count',
		'query_var'             	 => true,
		'rewrite'               	 => array( 'slug' => 'language' ),
	);

	register_taxonomy( 'language', MDB_POST_TYPE, $args );

	$labels = array(
		'name'                       => _x( 'Production Companies', 'taxonomy general name', 'mdb' ),
		'singular_name'              => _x( 'Production Company', 'taxonomy singular name', 'mdb' ),
		'search_items'               => __( 'Search production companies', 'mdb' ),
		'popular_items'              => __( 'Popular production companies', 'mdb' ),
		'all_items'                  => __( 'All production companies', 'mdb' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Production Company', 'mdb' ),
		'update_item'                => __( 'Update Production Company', 'mdb' ),
		'add_new_item'               => __( 'Add New Production Company', 'mdb' ),
		'new_item_name'              => __( 'New Production Company Name' ),
		'separate_items_with_commas' => __( 'Separate production companies with commas', 'mdb' ),
		'add_or_remove_items'        => __( 'Add or remove production companies', 'mdb' ),
		'choose_from_most_used'      => __( 'Choose from the most used production companies', 'mdb' ),
		'not_found'                  => __( 'No production companies found.', 'mdb' ),
		'menu_name'                  => __( 'Production Companies', 'mdb' ),
	);

	$args = array(
		'hierarchical'          	 => false,
		'labels'                	 => $labels,
		'show_ui'               	 => true,
		'show_admin_column'     	 => true,
		'update_count_callback' 	 => '_update_post_term_count',
		'query_var'             	 => true,
		'rewrite'               	 => array( 'slug' => 'production-company' ),
	);

	register_taxonomy( 'production-company', MDB_POST_TYPE, $args );

	
	$labels = array(
		'name' 						 => __( 'Movies', 'mdb' ),
		'singular_name' 			 => __( 'Movie', 'mdb' ),
		'add_new' 					 => __( 'Add New' ),
		'add_new_item' 				 => __( 'Add New Movie', 'mdb' ),
		'edit_item' 				 => __( 'Edit Movie', 'mdb' ),
		'new_item' 					 => __( 'New movie', 'mdb' ),
		'all_items' 				 => __( 'All movies', 'mdb' ),
		'view_item' 				 => __( 'View movie', 'mdb' ),
		'search_items' 				 => __( 'Search movies', 'mdb' ),
		'not_found' 				 => __( 'No movies found', 'mdb' ),
		'not_found_in_trash' 		 => __( 'No movies found in trash', 'mdb' ),
		'menu_name' 				 => __( 'Movies', 'mdb' )
	);

	$args = array(
		'labels' 					 => $labels,
		'public' 					 => true,
		'publicly_queryable' 		 => true,
		'show_ui' 					 => true, 
		'show_in_menu' 				 => true, 
		'query_var' 				 => true,
		'rewrite' 					 => array( 'slug' => 'movie' ),
		'capability_type' 			 => 'post',
		'has_archive' 				 => true, 
		'hierarchical' 				 => false,
		'menu_icon'					 => 'dashicons-video-alt',
		'supports' 					 => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
		'taxonomies' 				 => array( 'genere', 'country', 'language', 'production-company' )
	); 

	if( 'movie' == MDB_POST_TYPE ) register_mdb_Post_type( 'movie', $args );




	$labels = array(
		'name'                       => _x( 'Roles', 'taxonomy general name', 'mdb' ),
		'singular_name'              => _x( 'Role', 'taxonomy singular name', 'mdb' ),
		'search_items'               => __( 'Search roles', 'mdb' ),
		'popular_items'              => __( 'Popular roles', 'mdb' ),
		'all_items'                  => __( 'All roles', 'mdb' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Role', 'mdb' ),
		'update_item'                => __( 'Update Role', 'mdb' ),
		'add_new_item'               => __( 'Add New Role', 'mdb' ),
		'new_item_name'              => __( 'New Role Name' ),
		'separate_items_with_commas' => __( 'Separate roles with commas', 'mdb' ),
		'add_or_remove_items'        => __( 'Add or remove roles', 'mdb' ),
		'choose_from_most_used'      => __( 'Choose from the most used roles', 'mdb' ),
		'not_found'                  => __( 'No roles found.', 'mdb' ),
		'menu_name'                  => __( 'Roles', 'mdb' ),
	);

	$args = array(
		'hierarchical'          	 => false,
		'labels'                	 => $labels,
		'show_ui'               	 => true,
		'show_admin_column'     	 => true,
		'update_count_callback' 	 => '_update_post_term_count',
		'query_var'             	 => true,
		'rewrite'               	 => array( 'slug' => 'role' ),
	);

	register_taxonomy( 'role', null, $args );


	$labels = array(
		'name' 						 => __( 'Profiles', 'mdb' ),
		'singular_name' 			 => __( 'Profile', 'mdb' ),
		'add_new' 					 => __( 'Add New' ),
		'add_new_item' 				 => __( 'Add New Profile', 'mdb' ),
		'edit_item' 				 => __( 'Edit Profile', 'mdb' ),
		'new_item' 					 => __( 'New Profile', 'mdb' ),
		'all_items' 				 => __( 'Profiles', 'mdb' ),
		'view_item' 				 => __( 'View Profile', 'mdb' ),
		'search_items' 				 => __( 'Search Profiles', 'mdb' ),
		'not_found' 				 => __( 'No profiles found', 'mdb' ),
		'not_found_in_trash' 		 => __( 'No profiles found in trash', 'mdb' ),
		'menu_name' 				 => __( 'Profiles', 'mdb' )
	);

	$args = array(
		'labels' 					 => $labels,
		'public' 					 => true,
		'publicly_queryable' 		 => true,
		'show_ui' 					 => true, 
		'show_in_menu' 				 => true, 
		'query_var' 				 => true,
		'rewrite' 					 => array( 'slug' => 'profile' ),
		'capability_type' 			 => 'post',
		'has_archive' 				 => true, 
		'hierarchical' 				 => false,
		'show_in_menu'				 => MDB_POST_TYPE == 'post' ? 'edit.php' : 'edit.php?mdb_Post_type='.MDB_POST_TYPE,
		'supports' 					 => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'taxonomies' 				 => array( 'role' )
	); 

	register_post_type( 'profile', $args );
  
}
add_action( 'init', 'mdb_register_things' );

/**
 * Rename the excerpt metabox to summery for movie post type
 */
function mdb_change_excerpt_box_title() {
	remove_meta_box( 'postexcerpt', 'movie', 'side' );
	add_meta_box('postexcerpt', __( 'Summery', 'mdb' ), 'post_excerpt_meta_box', 'movie', 'normal', 'high');
}
if( 'movie' == MDB_POST_TYPE ) add_action( 'admin_init',  'mdb_change_excerpt_box_title' );


/**
 * Register all metaboxes related to movie database
 */
function mdb_add_metabox(){
	/* Metaboxes for Movie post type */
	add_meta_box( 'mdb-movie-details', __( 'Movie details', 'mdb' ), 'mdb_movie_details_metabox_content', MDB_POST_TYPE, 'normal', 'high', null );
	add_meta_box( 'mdb-movie-box-office', __( 'Box Office', 'mdb' ), 'mdb_movie_box_office_metabox_content', MDB_POST_TYPE, 'normal', 'high', null );
	add_meta_box( 'mdb-movie-crew', __( 'Movie Crew', 'mdb' ), 'mdb_movie_crew_metabox_content', MDB_POST_TYPE, 'normal', 'high', null );
	add_meta_box( 'mdb-movie-cast', __( 'Cast', 'mdb' ), 'mdb_movie_cast_metabox_content', MDB_POST_TYPE, 'normal', 'high', null );

	/* Metaboxes for Profile post type */
	add_meta_box(
		'mdb-profile-birth-info',
		__( 'Birth Information', 'mdb' ),
		'mdb_profile_birth_info_metabox_content',
		'profile',
		'normal',
		'high',
		null );

	add_meta_box(
		'mdb-profile-known-for',
		__( 'Known For', 'mdb' ),
		'mdb_movie_known_for_metabox_content',
		'profile',
		'normal',
		'high',
		null );
}
add_action( 'add_meta_boxes', 'mdb_add_metabox' );


/**
 * Output the metabox content for movie details
 */
function mdb_movie_details_metabox_content(){
	global $post;
	$details = get_post_meta( $post->ID, '_movie_details', true );
	$has_data = true;
	if( !is_array( $details ) ) $has_data = false;

?>
	<table>
		<tbody>
			<tr>
				<th><label for="movie-details-trailer"><?php _e( 'Movie Trailer', 'mdb' ); ?></label></th>
				<td>
					<input name="movie_details[trailer]" type="text" class="fullwidth" id="movie-details-trailer" value="<?php echo $has_data?$details['trailer']:''; ?>">
					<small class="desc"><?php _e( 'Link to movie trailer, i.e. on youtube.', 'mdb' ); ?></small>
				</td>
			</tr>
			<tr>
				<th><label for="movie-details-release"><?php _e( 'First Release', 'mdb' ); ?></label></th>
				<td>
					<input name="movie_details[release]" type="text" class="fullwidth" id="movie-details-release" value="<?php echo $has_data?$details['release']:''; ?>">
					<small class="desc"><?php _e( 'When and where the movie was first released.', 'mdb' ); ?></small>
				</td>
			</tr>
			<tr>
				<th><label for="movie-details-year"><?php _e( 'Year', 'mdb' ); ?></label></th>
				<td>
					<input name="movie_details[year]" type="text" class="fullwidth" id="movie-details-year" value="<?php echo $has_data?$details['year']:''; ?>">
					<small class="desc"><?php _e( 'Movie release year', 'mdb' ); ?></small>
				</td>
			</tr>
			<tr>
				<th><label for="movie-details-length"><?php _e( 'Length', 'mdb' ); ?></label></th>
				<td>
					<input name="movie_details[length]" type="text" class="fullwidth" id="movie-details-length" value="<?php echo $has_data?$details['length']:''; ?>">
					<small class="desc"><?php _e( 'Length of movie including unit', 'mdb' ); ?></small>
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Official Sites', 'mdb' ); ?></label></th>
				<td>
					<ul id="official-sites-input-list">
						<?php if( $has_data && sizeof( $details['official-sites'] ) > 0 ): ?>
						<?php $i = 0; ?>
						<?php foreach ($details['official-sites'] as $site ): ?>
							<li>
								<input 
									type="text" 
									placeholder="<?php _e('Name', 'mdb'); ?>" 
									name="movie_details[official-sites][<?php echo $i; ?>][name]" 
									class="small-input" 
									name_format="movie_details[official-sites][%d][name]" 
									value="<?php echo $site['name']; ?>">
								<input 
									type="text" 
									placeholder="<?php _e('URL', 'mdb'); ?>" 
									name="movie_details[official-sites][<?php echo $i; ?>][value]" 
									class="learge-input" 
									name_format="movie_details[official-sites][%d][value]" 
									value="<?php echo $site['value']; ?>">
								<input type="button" value="+" class="add-site button">
								<input type="button" value="-" class="remove-site button">
							</li>
							<?php $i++; ?>
						<?php endforeach; ?>
						<?php else: ?>
							<li>
								<input type="text" placeholder="<?php _e('Name', 'mdb'); ?>" name="movie_details[official-sites][0][name]" class="small-input" name_format="movie_details[official-sites][%d][name]">
								<input type="text" placeholder="<?php _e('URL', 'mdb'); ?>" name="movie_details[official-sites][0][value]" class="learge-input" name_format="movie_details[official-sites][%d][value]">
								<input type="button" value="+" class="add-site button">
								<input type="button" value="-" class="remove-site button">
							</li>
						<?php endif; ?>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<?php wp_nonce_field( 'movie_details', 'movie_details[nonce]', false ); ?>
<?php

}

/**
 * Output the metabox content for movie box office information
 */
function mdb_movie_box_office_metabox_content(){
	global $post;
	$details = get_post_meta( $post->ID, '_movie_box_office', true );
	$has_data = true;
	if( !is_array( $details ) ) $has_data = false;

?>
	<table>
		<tbody>
			<tr>
				<th><label for="movie-box-office-budget"><?php _e( 'Budget', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="movie_box_office[budget]" 
						type="text" 
						class="fullwidth" 
						id="movie-box-office-budget" 
						value="<?php echo $has_data?$details['budget']:''; ?>">
				</td>
			</tr>
			<tr>
				<th><label for="movie-box-office-opening_weekend"><?php _e( 'Opening Weekend', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="movie_box_office[opening_weekend]" 
						type="text" 
						class="fullwidth" 
						id="movie-box-office-opening_weekend" 
						value="<?php echo $has_data?$details['opening_weekend']:''; ?>">

					<small class="desc"><?php _e( 'Profit in opening weekend.', 'mdb' ); ?></small>
				</td>
			</tr>
			<tr>
				<th><label for="movie-box-office-gross"><?php _e( 'Gross', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="movie_box_office[gross]" 
						type="text" 
						class="fullwidth" 
						id="movie-box-office-gross" 
						value="<?php echo $has_data?$details['gross']:''; ?>">
					<small class="desc"><?php _e( 'Gross profit.', 'mdb' ); ?></small>
				</td>
			</tr>
		</tbody>
	</table>
	<?php wp_nonce_field( 'movie_box_office', 'movie_box_office[nonce]', false ); ?>
<?php

}


/**
 * To return the url of a profile thumb of a given profile.
 * 
 * Simply returns the url of the profile thumb for any cast and crew
 * Which is the custom size, small-thumb(50x50). It will return link to the
 * mistryman.jpg file which is the default image if a profile picture is
 * not available.
 * 
 * @param int $post_id Post id of the profile
 */
function mdb_get_profile_thumb( $post_id ){
	if( has_post_thumbnail( $post_id ) ){
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'small-thumb' );

		return $thumb[0];
	}

	return plugins_url( 'img/mistryman.jpg', __FILE__ );
}


/**
 * To return the url of a movie poster of a given movie.
 * 
 * Simply returns the url of the movie poster for any movie
 * Which is the custom size, small-thumb(50x50) by default. It will return link to the
 * defaultposter.jpg file which is the default image if a profile picture is
 * not available.
 * 
 * @param int $post_id Post id of the profile.
 * @param string $size Media size of the image, not applicable for default poster.
 */
function mdb_get_movie_poster( $post_id, $size = 'small-thumb' ){
	if( has_post_thumbnail( $post_id ) ){
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );

		return $thumb[0];
	}

	return plugins_url( 'img/defaultposter.jpg', __FILE__ );
}


/**
 * Output the metabox content for movie crew
 */
function mdb_movie_crew_metabox_content(){
	global $post, $wpdb;
	
	$crews = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}movie_cast_n_crew` 
			WHERE `movie`='$post->ID' AND `type`='crew';" );
?>
	<table id="crew-list">
			<tbody>
				<?php if ( sizeof( $crews ) > 0 ): ?>
				<?php $i = 0; ?>
				<?php foreach ( $crews as $crew_entry ): ?>
					<?php $profile = get_post( $crew_entry->profile ) ?>
					<tr>
						<td class="thumb">
							<img src="<?php echo mdb_get_profile_thumb( $profile->ID ); ?>" alt="Thumbnail" width="50" height="50">
						</td>
						<td>
							<input 
								type="text" 
								name="movie_crew[<?php echo $i; ?>][name]" 
								name_format="movie_crew[%d][name]" 
								class="fullwidth mdb-profile" 
								placeholder="<?php _e( 'Name', 'mdb' ); ?>"
								value="<?php echo $profile->post_title; ?>">
						</td>
						<td>
							<input 
								type="text" 
								name="movie_crew[<?php echo $i; ?>][role]" 
								name_format="movie_crew[%d][role]" 
								class="fullwidth" 
								placeholder="<?php _e( 'Role', 'mdb' ); ?>"
								value="<?php echo $crew_entry->role; ?>">
						</td>
						<td>
							<input 
								type="text" 
								name="movie_crew[<?php echo $i; ?>][task]" 
								name_format="movie_crew[%d][task]" 
								class="fullwidth" 
								placeholder="<?php _e( 'Task (optional)', 'mdb' ); ?>"
								value="<?php echo $crew_entry->task; ?>">
						</td>
						<td class="crew_list_resizer">
							<input type="button" value="+" class="add-crew button">
							<input type="button" value="-" class="remove-crew button">
							<input 
								type="hidden" 
								name="movie_crew[<?php echo $i; ?>][id]" 
								value="<?php echo $profile->ID; ?>" 
								name_format="movie_crew[%d][id]" 
								class="profile_id">
						</td>
					</tr>
					<?php $i++; ?>
				<?php endforeach ?>
				<?php else: ?>
				<tr>
					<td class="thumb">
						<img src="<?php echo plugins_url( 'img/mistryman.jpg', __FILE__ ); ?>" alt="Thumbnail" width="50" height="50">
					</td>
					<td>
						<input type="text" name="movie_crew[0][name]" name_format="movie_crew[%d][name]" class="fullwidth mdb-profile" placeholder="<?php _e( 'Name', 'mdb' ); ?>">
					</td>
					<td>
						<input type="text" name="movie_crew[0][role]" name_format="movie_crew[%d][role]" class="fullwidth" placeholder="<?php _e( 'Role', 'mdb' ); ?>">
					</td>
					<td>
						<input type="text" name="movie_crew[0][task]" name_format="movie_crew[%d][task]" class="fullwidth" placeholder="<?php _e( 'Task (optional)', 'mdb' ); ?>">
					</td>
					<td class="crew_list_resizer">
						<input type="button" value="+" class="add-crew button">
						<input type="button" value="-" class="remove-crew button">
						<input type="hidden" name="movie_crew[0][id]" value="0" name_format="movie_crew[%d][id]" class="profile_id">
					</td>
				</tr>
				<?php endif ?>
			</tbody>
		</table>
	<?php wp_nonce_field( 'movie_crew', 'movie_crew[nonce]', false ); ?>
<?php

}

/**
 * Output the metabox content for movie cast
 */
function mdb_movie_cast_metabox_content(){
	global $post, $wpdb;
	
	$casts = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}movie_cast_n_crew` 
			WHERE `movie`='$post->ID' AND `type`='cast';" );
?>
	<table id="cast-list">
		<tbody>
			<?php if ( sizeof( $casts ) > 0 ): ?>
			<?php $i = 0; ?>
			<?php foreach ( $casts as $cast_entry ): ?>
				<?php $profile = get_post( $cast_entry->profile ) ?>
				<tr>
					<td class="thumb">
						<img src="<?php echo mdb_get_profile_thumb( $profile->ID ); ?>" alt="Thumbnail" width="50" height="50">
					</td>
					<td>
						<input 
							type="checkbox" 
							name="movie_cast[<?php echo $i; ?>][featured]" 
							name_format="movie_cast[%d][featured]" 
							title="<?php _e( 'Featured', 'appex' ); ?>" 
							<?php echo $cast_entry->featured == 1 ? 'checked="checked"': ''; ?>>
					</td>
					<td>
						<input 
							type="text" 
							name="movie_cast[<?php echo $i; ?>][name]" 
							name_format="movie_cast[%d][name]" 
							class="fullwidth mdb-profile" 
							placeholder="<?php _e('Name', 'mdb'); ?>"
							value="<?php echo $profile->post_title; ?>">
					</td>
					<td>
						<input 
							type="text" 
							name="movie_cast[<?php echo $i; ?>][role]" 
							name_format="movie_cast[%d][role]" 
							class="fullwidth" 
							placeholder="<?php _e('Role', 'mdb'); ?>"
							value="<?php echo $cast_entry->role; ?>">
					</td>
					<td class="cast_list_resizer">
						<td class="cast_list_resizer">
							<input type="button" value="+" class="add-artist button">
							<input type="button" value="-" class="remove-artist button">
							<input 
								type="hidden" 
								name="movie_cast[<?php echo $i; ?>][id]" 
								value="<?php echo $profile->ID; ?>" 
								name_format="movie_cast[%d][id]" 
								class="profile_id">
						</td>
					</td>
				</tr>
				<?php $i++; ?>
			<?php endforeach ?>
			<?php else: ?>
			<tr>
				<td class="thumb">
					<img src="<?php echo plugins_url( 'img/mistryman.jpg', __FILE__ ); ?>" alt="<?php _e('Thumbnail', 'mdb'); ?>" width="50" height="50">
				</td>
				<td>
					<input 
						type="checkbox" 
						name="movie_cast[0][featured]" 
						name_format="movie_cast[%d][featured]" 
						title="<?php _e( 'Featured', 'appex' ); ?>">
				</td>
				<td>
					<input type="text" name="movie_cast[0][name]" name_format="movie_cast[%d][name]" class="fullwidth mdb-profile" placeholder="<?php _e('Name', 'mdb'); ?>">
				</td>
				<td>
					<input type="text" name="movie_cast[0][role]" name_format="movie_cast[%d][role]" class="fullwidth" placeholder="<?php _e('Role', 'mdb'); ?>">
				</td>
				<td class="cast_list_resizer">
					<input type="button" value="+" class="add-artist button">
					<input type="button" value="-" class="remove-artist button">
					<input type="hidden" name="movie_cast[0][id]" value="0" name_format="movie_cast[%d][id]" class="profile_id">
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php wp_nonce_field( 'movie_cast', 'movie_cast[nonce]', false ); ?>
<?php

}

/**
 * Output the metabox content for known for metabox in profile post type.
 */
function mdb_movie_known_for_metabox_content(){
	global $post;
	$known_for = get_post_meta( $post->ID, '_profile_known_for', true );
?>
	<table id="known-for-list">
		<tbody>
			<?php if( !empty( $known_for ) ): ?>
			<?php $i = 0; ?>
			<?php foreach( $known_for as $movie_id ): ?>
			<?php $movie = get_post( $movie_id ); ?>
				<tr>
					<td class="thumb">
						<img src="<?php echo mdb_get_movie_poster( $movie->ID ); ?>" alt="Thumbnail" width="50" height="50">
					</td>
					<td>
						<input 
							type="text" 
							name="profile_known_for[<?php echo $i; ?>][name]" 
							name_format="profile_known_for[%d][name]" 
							class="fullwidth mdb-movie" 
							placeholder="Title"
							value="<?php echo $movie->post_title; ?>">
					</td>
					<td class="movie_list_resizer">
						<td class="cast_list_resizer">
							<input type="button" value="+" class="add-movie button">
							<input type="button" value="-" class="remove-movie button">
							<input 
								type="hidden" 
								name="profile_known_for[<?php echo $i; ?>][id]" 
								value="<?php echo $movie->ID; ?>" 
								name_format="profile_known_for[%d][id]" 
								class="movie_id">
						</td>
					</td>
				</tr>
				<?php $i++; ?>
			<?php endforeach ?>
			<?php else: ?>
			<tr>
				<td class="thumb">
					<img src="<?php echo plugins_url( 'img/defaultposter.jpg', __FILE__ );?>" alt="Thumbnail" width="50" height="50">
				</td>
				<td>
					<input 
						type="text" 
						name="profile_known_for[<?php echo $i; ?>][name]" 
						name_format="profile_known_for[%d][name]" 
						class="fullwidth mdb-movie" 
						placeholder="Title"
						value="">
				</td>
				<td class="movie_list_resizer">
					<td class="cast_list_resizer">
						<input type="button" value="+" class="add-movie button">
						<input type="button" value="-" class="remove-movie button">
						<input 
							type="hidden" 
							name="profile_known_for[<?php echo $i; ?>][id]" 
							value="0" 
							name_format="profile_known_for[%d][id]" 
							class="movie_id">
					</td>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php wp_nonce_field( 'profile_known_for', 'profile_known_for[nonce]', false ); ?>
<?php

}

/**
 * Provides the content for birth info metabox for profile post type
 * 
 * This metabox holdes birth related information i.e. Place and Date of birth, Born name for a
 * movie person.
 */
function mdb_profile_birth_info_metabox_content(){
	global $post;
	$details = get_post_meta( $post->ID, '_profile_birth_info', true );
	$has_data = true;
	if( !is_array( $details ) ) $has_data = false;

?>
	<table>
		<tbody>
			<tr>
				<th><label for="profile-birth-info-place"><?php _e( 'Birth Place', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="profile_birth_info[place]" 
						type="text" 
						class="fullwidth" 
						id="profile-birth-info-place" 
						value="<?php echo $has_data?$details['place']:''; ?>">
				</td>
			</tr>
			<tr>
				<th><label for="profile-birth-info-date"><?php _e( 'Birth Date', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="profile_birth_info[date]" 
						type="text" 
						class="fullwidth" 
						id="profile-birth-info-date" 
						value="<?php echo $has_data?$details['date']:''; ?>">
				</td>
			</tr>
			<tr>
				<th><label for="profile-birth-info-born-name"><?php _e( 'Born Name', 'mdb' ); ?></label></th>
				<td>
					<input 
						name="profile_birth_info[born_name]" 
						type="text" 
						class="fullwidth" 
						id="profile-birth-info-born-name" 
						value="<?php echo $has_data?$details['born_name']:''; ?>">
				</td>
			</tr>
		</tbody>
	</table>
	<?php wp_nonce_field( 'profile_birth_info', 'profile_birth_info[nonce]', false ); ?>
<?php
}


/**
 * Save postmeta for post types created by this plugin.
 * 
 * @param intg $post_id ID of the post being saved
 * @uses mdb_save_movie_meta For movie post type
 * @uses mdb_save_profile_meta For profile post type
 */
function mdb_save_meta( $post_id ) {
	$post = get_post( $post_id );

	if( MDB_POST_TYPE == $post->post_type ){
		mdb_save_movie_meta( $post );
		mdb_create_message_board( $post );
	}
	if( 'profile' == $post->post_type ) mdb_save_profile_meta( $post );
}
add_action( 'save_post', 'mdb_save_meta' );


/**
 * Creates a messaging board.
 * 
 * Create message board/bbPress forum for each movie when the message is first created.
 * @param object $post WP Post object for the submitted post.
 */
function mdb_create_message_board( $post ){
	$forum_id = get_post_meta( $post->ID, '_mdb_message_board_id', true );

	if( '' == $forum_id && 'publish' == $post->post_status  ){
		$forum_id = wp_insert_post( array(
			'post_title'    => $post->post_title,
			'post_type'     => 'forum',
			'post_status'   => 'publish' ) );

		add_post_meta( $forum_id, '_mdb_movie_id', $post->ID );
		add_post_meta( $post->ID, '_mdb_message_board_id', $forum_id );
	}
}


/**
 * Get topics for a movie
 */
function mdb_get_topics( $movie_id ){
	$forum_id = get_post_meta( $movie_id, '_mdb_message_board_id', true );

	return get_posts( array(
		'post_type' => 'topic',
		'post_parent' => $forum_id,
		'post_status' => 'publish',
		'posts_per_page' => 10
	) );
}


/**
 * Save meta information related to a movie
 * 
 * @param object $movie Post object of the movie being saved
 */
function mdb_save_movie_meta( $movie ){
	global $wpdb;

	if( isset( $_POST['movie_details'] ) && wp_verify_nonce( $_POST['movie_details']['nonce'], 'movie_details' ) ){
		unset( $_POST['movie_details']['nonce'] );
		$meta = $_POST['movie_details'];

		update_post_meta( $movie->ID, '_movie_details', $meta );
	}

	if( isset( $_POST['movie_box_office'] ) && wp_verify_nonce( $_POST['movie_box_office']['nonce'], 'movie_box_office' ) ){
		unset( $_POST['movie_box_office']['nonce'] );
		$meta = $_POST['movie_box_office'];

		update_post_meta( $movie->ID, '_movie_box_office', $meta );
	}

	if( isset( $_POST['movie_crew'] ) && wp_verify_nonce( $_POST['movie_crew']['nonce'], 'movie_crew' ) ){
		unset( $_POST['movie_crew']['nonce'] );
		$crews = $_POST['movie_crew'];

		$wpdb->query( "DELETE FROM `{$wpdb->prefix}movie_cast_n_crew` WHERE `movie`='$movie->ID' AND `type`='crew';");
		foreach( $crews as $crew ){
			if( '' != $crew['id'] && 0 != $crew['id'] ){
				$id = $crew['id'];
			}
			elseif( strlen( trim( $crew['name'] ) ) >= 3 ){
				$id = wp_insert_post( array(
					'post_title'	=> trim( $crew['name'] ),
					'post_type'		=> 'profile',
					'post_status'	=> 'draft' ) );
			}

			if( isset( $id ) ){
				$wpdb->query( "INSERT INTO `{$wpdb->prefix}movie_cast_n_crew` (`movie`, `profile`, `role`, `type`, `task`)
					VALUES
						($movie->ID, $id, '$crew[role]', 'crew', '$crew[task]');" );
				unset( $id );
			}

		}
	}

	if( isset( $_POST['movie_cast'] ) && wp_verify_nonce( $_POST['movie_cast']['nonce'], 'movie_cast' ) ){
		unset( $_POST['movie_cast']['nonce'] );
		$casts = $_POST['movie_cast'];

		$wpdb->query( "DELETE FROM `{$wpdb->prefix}movie_cast_n_crew` WHERE `movie`='$movie->ID' AND `type`='cast';");
		foreach( $casts as $cast ){
			if( '' != $cast['id'] && 0 != $cast['id'] ){
				$id = $cast['id'];
			}
			elseif( strlen( trim( $cast['name'] ) ) >= 3 ){
				$id = wp_insert_post( array(
					'post_title'	=> trim( $cast['name'] ),
					'post_type'		=> 'profile',
					'post_status'	=> 'draft' ) );
			}

			if( isset( $id ) ){
				$featured = isset( $cast['featured'] ) && $cast['featured'] == 'on' ? 1 : 0;

				$wpdb->query( "INSERT INTO `{$wpdb->prefix}movie_cast_n_crew` (`movie`, `profile`, `role`, `type`, `featured`)
					VALUES
						($movie->ID, $id, '$cast[role]', 'cast', '$featured');" );
				unset( $id );
			}

		}
	}
}


/**
 * Save meta information related to a profile
 * 
 * @param object $profile Post object of the profile being saved
 */
function mdb_save_profile_meta( $profile ){
	if( isset( $_POST['profile_birth_info'] ) && wp_verify_nonce( $_POST['profile_birth_info']['nonce'], 'profile_birth_info' ) ){
		unset( $_POST['profile_birth_info']['nonce'] );
		$meta = $_POST['profile_birth_info'];

		update_post_meta( $profile->ID, '_profile_birth_info', $meta );
	}

	if( isset( $_POST['profile_known_for'] ) && wp_verify_nonce( $_POST['profile_known_for']['nonce'], 'profile_known_for' ) ){
		unset( $_POST['profile_known_for']['nonce'] );
		$movies = $_POST['profile_known_for'];

		$known_for = array();
		foreach( $movies as $movie ){
			if( isset( $movie['id'] ) && '' != $movie['id'] && 0 != $movie['id'] ){
				$id = $movie['id'];
				$known_for[] = $id;
			}
			elseif( isset( $movie['name'] ) && strlen( trim( $movie['name'] ) ) >= 3 ){
				$id = wp_insert_post( array(
					'post_title'	=> trim( $movie['name'] ),
					'post_type'		=> MDB_POST_TYPE,
					'post_status'	=> 'draft' ) );
				
				$known_for[] = $id;
			}


		}

		update_post_meta( $profile->ID, '_profile_known_for', $known_for );
	}
}


/**
 * Provides a list of profiles or movies of similar name for ajax call.
 * 
 * @param string $term What the title should be like. It will be collected from $_REQUEST if not specified.
 */
function mdb_get_posts_like( $term = null ){
	if( null == $term ) $term = $_REQUEST['term'];

	header('Content-type: application/json');

	if( 'get-profiles' == $_REQUEST['action'] ) $post_type = 'profile';
	if( 'get-movies' == $_REQUEST['action'] ) $post_type = MDB_POST_TYPE;

	$posts = get_posts(
		array(
			'post_status'		=> array( 'publish', 'draft' ),
			'post_title_like'	=> trim( $term ),
			'post_type'			=> $post_type,
			'suppress_filters'	=> false ) );

	$custom_posts = array();
	foreach( $posts as $post ){
		$custom_post = array();
		$custom_post['ID']		= $post->ID;
		$custom_post['label']	= $post->post_title;
		
		if( 'profile' == $post_type ) $custom_post['thumb']		= mdb_get_profile_thumb( $post->ID );
		if( MDB_POST_TYPE == $post_type ) $custom_post['thumb']		= mdb_get_movie_poster( $post->ID );
		
		$custom_posts[] = $custom_post;
	}

	echo json_encode( $custom_posts );
	exit;
}
add_action( 'wp_ajax_get-profiles', 'mdb_get_posts_like' );
add_action( 'wp_ajax_nopriv_get-profiles', 'mdb_get_posts_like' );
add_action( 'wp_ajax_get-movies', 'mdb_get_posts_like' );
add_action( 'wp_ajax_nopriv_get-movies', 'mdb_get_posts_like' );


/**
 * Row data from database query to get filmography information of a profile.
 */
function mdb_get_filmography_data( $post = null ){
	if( null == $post ) global $post;
	if( is_integer( $post ) ) $post = get_post( $post );

	global $wpdb;

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}movie_cast_n_crew WHERE profile='$post->ID' ORDER BY type DESC" );

	$data = array();
	foreach( $results as $result ){
		$set = $result->role;

		if( '' == $result->role ) $set = __( 'Miscellaneous', 'mdb' );
		if( 'cast' == $result->type ) $set = __( 'Artist', 'mdb' );

		$data[$set][] = $result; // Just grouping rows by roles
	}

	return $data;
}


/**
 * Row data from database query to get crew information of a movie
 */
function mdb_get_movie_crew( $post = null ){
	if( null == $post ) global $post;
	if( is_integer( $post ) ) $post = get_post( $post );

	global $wpdb;

	$results = $wpdb->get_results( "SELECT *
		FROM {$wpdb->prefix}movie_cast_n_crew 
		WHERE (
			movie='$post->ID'
			AND
			(
				type='crew'
				OR
				(
					type='cast'
					AND
					featured=1
				)
			)
		) ORDER BY type DESC" );

	$data = array();
	foreach( $results as $result ){
		$set = $result->role;

		if( '' == $result->role ) $set = __( 'Miscellaneous', 'mdb' );
		if( 'cast' == $result->type ) $set = __( 'Stars', 'mdb' );

		$data[$set][] = $result; // Just grouping rows by roles
	}

	unset( $data[__( 'Miscellaneous', 'mdb' )] ); // Remove Miscellaneous crew (they are not important)

	return $data;
}

/**
 * Get the list of crew from database assigned to a particular movie
 */
function mdb_get_cast_list( $post = null ){
	global $wpdb;
	if( null == $post ) global $post;
	if( is_integer( $post ) ) $post = get_post( $post );
	if( !is_object( $post ) || MDB_POST_TYPE != $post->post_type ) return false;

	$casts = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}movie_cast_n_crew` 
			WHERE `movie`='$post->ID' AND `type`='cast' ORDER BY featured DESC;" );

	return $casts;
}


/**
 * Returns the overall rating information html for a movie.
 */
function mdb_review_block( $post = null ){
	if( null == $post ) global $post;
	if( is_integer( $post ) ) $post = get_post( $post );
	if( !is_object( $post ) || MDB_POST_TYPE != $post->post_type ) return false;

	global $wpdb;
	$rating = $wpdb->get_row("
		SELECT AVG($wpdb->commentmeta.meta_value) as rating_average, COUNT(*) as rating_count
		FROM $wpdb->commentmeta
		INNER JOIN $wpdb->comments on $wpdb->comments.comment_id=$wpdb->commentmeta.comment_id
		WHERE $wpdb->commentmeta.meta_key='movie_rating' 
		AND $wpdb->comments.comment_post_id=$post->ID 
		AND $wpdb->comments.comment_approved =1 ;");

	?>
	<div class="mdb-rating-stars" data-readonly="true" data-value="<?php echo $rating->rating_average; ?>"></div>
	<div class="mdb-rating-info">
		<?php 
		echo "$rating->rating_average/10, $rating->rating_count ratings.";
		?>
	</div>
	<?php
}

/**
 * Displays attachment thumbnails in movies
 */
function mdb_movie_attachment_block( $post = null ){
	if( null == $post ) global $post;
	if( is_integer( $post ) ) $post = get_post( $post );
	if( !is_object( $post ) || MDB_POST_TYPE != $post->post_type ) return false;

	ob_start();
	
	echo '<div class="mdb-attachment-wrapper">';

	$attachments = get_posts( array(
	    'post_type' => 'attachment',
	    'posts_per_page' => -1,
	    'post_parent' => $post->ID,
	    'exclude'     => get_post_thumbnail_id() ) );

	if ( $attachments ) {
	    foreach ( $attachments as $attachment ) {
	    	$image_src = wp_get_attachment_image_src( $attachment->ID, 'full' );


	        echo '<div>' 
	        . '<a href="' . $image_src[0] . '" class="fancybox">'
	        . wp_get_attachment_image( $attachment->ID, 'thumbnail' )
	        . '</a>'
	        . '</div>';
	    }
	}

	echo '</div>';
}


/**
 * Echoes javascript in front end wp_footer.
 */
function mdb_footer_js(){
?>
	<script>
	jQuery(document).ready(function($){
		<?php global $post; if( is_single() && MDB_POST_TYPE == $post->post_type ):?>
		$('.mdb-storyline').readmore();
		$('.mdb-attachment-wrapper').slick({
			arrows: true,
			dots: true,
			infinite: false,
			speed: 300,
			slidesToShow: 6,
			slidesToScroll: 4,
			lazyLoad: 'ondemand',
			responsive: [
			{
				breakpoint: 1025,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 2,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1,
					dots: false
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					dots: false
				}
			}]
		});
		$('.fancybox').fancybox({margin: 100});


		// Star rating
		var update_rating_display = function( el ){
			var width = $(el).attr('data-value') / 10 * 100;
			$(el).find('.progress').css('width', width+'%');
		}

		$('.mdb-rating-stars:not(.visible)')
			.addClass('visible')
			.prepend('<div class="progress"></div>')
			.each(function(){
				if('false' == $(this).attr('data-readonly'))
					$(this).append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>')
						.append('<span></span>');
				update_rating_display(this);
			});

		$('.mdb-rating-stars.visible').each(function(){
			var holder = this;

			$(holder).find('span').click(function(){
				$(holder).attr('data-value', $(this).index());
				update_rating_display(holder);

				var field = $(holder).attr('data-field');
				$(field).val($(this).index());
			});

		});

		<?php elseif( is_single() && 'profile' == $post->post_type ):?>
		$('.mdb-images').slick({
			arrows: true,
			dots: true,
			infinite: false,
			speed: 300,
			slidesToShow: 6,
			slidesToScroll: 4,
			lazyLoad: 'ondemand',
			responsive: [
			{
				breakpoint: 1025,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 2,
					dots: false
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1,
					dots: false
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					dots: false
				}
			}]
		});
		$('.mdb-known-for').slick({
			slidesToShow: 4,
			lazyLoad: 'ondemand',
			dots: false,
			responsive: [
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1
				}
			}]
		});

		$('.fancybox').fancybox({margin: 100});
		<?php endif; ?>
	});
	</script>
<?php
}
add_action('wp_footer', 'mdb_footer_js');


/**
 * Add the rating field in a movie
 */
function mdb_comment_form_rating_option( $comment ) {
	global $post;

	if( !isset( $comment->comment_ID ) ){
		$existing_rating = 0;
	}
	else {
		$existing_rating = get_comment_meta( $comment->comment_ID, 'movie_rating', true );
		if( '' == $existing_rating ) $existing_rating = 0;
	}


	if( MDB_POST_TYPE != $post->post_type ) return;
	?>
	<p class="comment-form-rating">
		<label for="movie-rating"><?php _e( 'Movie Rating', 'mdb' );?></label>
		<br>
		<div class="mdb-rating-stars" data-readonly="false" data-field="#movie-rating" data-value="<?php echo $existing_rating; ?>"></div>
		<input type="hidden" id="movie-rating" name="movie_rating" value="<?php echo $existing_rating; ?>" />
	</p>
	<?php
}
add_action( 'comment_form_logged_in_after', 'mdb_comment_form_rating_option' );
add_action( 'comment_form_after_fields', 'mdb_comment_form_rating_option' );


/**
 * Save the rating for a movie.
 */
function mdb_save_movie_rating( $comment_id, $comment ){
	if( !isset( $_POST['movie_rating'] ) ) return;

	$existing_ratings = get_comments(
		array(
			'post_id' => $comment->post_ID,
			'author_email' => $comment->comment_author_email,
			'meta_key' => 'movie_rating' ) );

	/**
	 * If there is already a review from this author, ignore.
	 */
	if( !empty( $existing_ratings ) ) return;

	/**
	 * Is rating too high or too low?
	 */
	$_POST['movie_rating'] = (int) $_POST['movie_rating'];
	$_POST['movie_rating'] = max( 0, $_POST['movie_rating'] );
	$_POST['movie_rating'] = min( 10, $_POST['movie_rating'] );

	/**
	 * Don't save if rating is not even 1 star.
	 */
	if( 1 > $_POST['movie_rating'] ) return;


	update_comment_meta( $comment_id, 'movie_rating', $_POST[ 'movie_rating' ] );
}
add_action( 'wp_insert_comment', 'mdb_save_movie_rating', 10, 2 );


/**
 * Display rating before the comment text
 */
function mdb_display_rating_comment( $comment_text, $comment ){
	$rating = get_comment_meta( $comment->comment_ID, 'movie_rating', true );
	$rating = max( 0, $rating );
	$rating = min( 10, $rating );
	if( $rating < 1 ) return $comment_text;

	return '<div style="clear:both"></div>' 
		. '<div class="mdb-rating-stars" data-readonly="true" data-value="'.$rating.'"></div>'
		. $comment_text;
}	
add_filter( 'comment_text', 'mdb_display_rating_comment', 10, 2 );


