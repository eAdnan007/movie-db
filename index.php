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
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}movie_cast_n_crew` (
		  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `movie` int(11) unsigned NOT NULL,
		  `profile` int(11) unsigned NOT NULL,
		  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `type` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
}
register_activation_hook(__FILE__, 'mdb_activated');

/**
 * Add featured image support to movies and profiles
 */
function mdb_support_featured_image(){
	add_theme_support( 'post_thumbnails', array( 'movie', 'profile' ) );
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
			'mistryman'		=> plugins_url( 'img/mistryman.jpg', __FILE__ ),
			'ajax_url'		=> admin_url( '/admin-ajax.php' ) ) );

	wp_enqueue_style( 'movie-db', plugins_url( 'css/movie-db.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'mdb_admin_enqueue' );


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

	register_taxonomy( 'genere', null, $args );

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

	register_taxonomy( 'country', null, $args );

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

	register_taxonomy( 'language', null, $args );

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

	register_taxonomy( 'production-company', null, $args );

	
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
		'supports' 					 => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'taxonomies' 				 => array( 'genere', 'country', 'language', 'production-company' )
	); 

	register_post_type( 'movie', $args );


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
		'show_in_menu'				 => 'edit.php?post_type=movie',
		'supports' 					 => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'taxonomies' 				 => array( 'genere', 'country', 'language', 'production-company' )
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
add_action( 'admin_init',  'mdb_change_excerpt_box_title' );


/**
 * Register all metaboxes related to movie database
 */
function mdb_add_metabox(){
	add_meta_box( 'mdb-movie-details', __( 'Movie details', 'mdb' ), 'mdb_movie_details_metabox_content', 'movie', 'normal', 'high', null );
	add_meta_box( 'mdb-movie-box-office', __( 'Box Office', 'mdb' ), 'mdb_movie_box_office_metabox_content', 'movie', 'normal', 'high', null );
	add_meta_box( 'mdb-movie-crew', __( 'Movie Crew', 'mdb' ), 'mdb_movie_crew_metabox_content', 'movie', 'normal', 'high', null );
	add_meta_box( 'mdb-movie-cast', __( 'Cast', 'mdb' ), 'mdb_movie_cast_metabox_content', 'movie', 'normal', 'high', null );
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
									placeholder="Name" 
									name="movie_details[official-sites][<?php echo $i; ?>][name]" 
									class="small-input" 
									name_format="movie_details[official-sites][%d][name]" 
									value="<?php echo $site['name']; ?>">
								<input 
									type="text" 
									placeholder="URL" 
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
								<input type="text" placeholder="Name" name="movie_details[official-sites][0][name]" class="small-input" name_format="movie_details[official-sites][%d][name]">
								<input type="text" placeholder="URL" name="movie_details[official-sites][0][value]" class="learge-input" name_format="movie_details[official-sites][%d][value]">
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
 * Output the metabox content for movie crew
 */
function mdb_movie_crew_metabox_content(){
	global $post;

?>
	<table id="crew-list">
			<tbody>
				<tr>
					<td class="thumb">
						<img src="<?php echo plugins_url( 'img/mistryman.jpg', __FILE__ ); ?>" alt="Thumbnail" width="50" height="50">
					</td>
					<td>
						<input type="text" name="movie_crew[0][name]" name_format="movie_crew[%d][name]" class="fullwidth mdb-profile" placeholder="Name">
					</td>
					<td>
						<input type="text" name="movie_crew[0][role]" name_format="movie_crew[%d][role]" class="fullwidth" placeholder="Role">
					</td>
					<td class="crew_list_resizer">
						<input type="button" value="+" class="add-crew button">
						<input type="button" value="-" class="remove-crew button">
						<input type="hidden" name="movie_crew[0][id]" value="0" name_format="movie_crew[%d][id]" class="profile_id">
					</td>
				</tr>
			</tbody>
		</table>
	<?php wp_nonce_field( 'movie_crew', 'movie_crew[nonce]', false ); ?>
<?php

}

/**
 * Output the metabox content for movie cast
 */
function mdb_movie_cast_metabox_content(){
	global $post;

?>
	<table id="cast-list">
			<tbody>
				<tr>
					<td class="thumb">
						<img src="<?php echo plugins_url( 'img/mistryman.jpg', __FILE__ ); ?>" alt="Thumbnail" width="50" height="50">
					</td>
					<td>
						<input type="text" name="movie_cast[0][name]" name_format="movie_cast[%d][name]" class="fullwidth mdb-profile" placeholder="Name">
					</td>
					<td>
						<input type="text" name="movie_cast[0][role]" name_format="movie_cast[%d][role]" class="fullwidth" placeholder="Role">
					</td>
					<td class="cast_list_resizer">
						<input type="button" value="+" class="add-artist button">
						<input type="button" value="-" class="remove-artist button">
						<input type="hidden" name="movie_cast[0][id]" value="0" name_format="movie_cast[%d][id]" class="profile_id">
					</td>
				</tr>
			</tbody>
		</table>
	<?php wp_nonce_field( 'movie_cast', 'movie_cast[nonce]', false ); ?>
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

	if( 'movie' == $post->post_type ) mdb_save_movie_meta( $post );
	if( 'profile' == $post->post_type ) mdb_save_profile_meta( $post );
}
add_action( 'save_post', 'mdb_save_meta' );


/**
 * Save meta information related to a movie
 * 
 * @param object $movie Post object of the movie being saved
 */
function mdb_save_movie_meta( $movie ){
	global $wpdb;

	if( wp_verify_nonce( $_POST['movie_details']['nonce'], 'movie_details' ) ){
		unset( $_POST['movie_details']['nonce'] );
		$meta = $_POST['movie_details'];

		update_post_meta( $movie->ID, '_movie_details', $meta );
	}

	if( wp_verify_nonce( $_POST['movie_box_office']['nonce'], 'movie_box_office' ) ){
		unset( $_POST['movie_box_office']['nonce'] );
		$meta = $_POST['movie_box_office'];

		update_post_meta( $movie->ID, '_movie_box_office', $meta );
	}

	if( wp_verify_nonce( $_POST['movie_crew']['nonce'], 'movie_crew' ) ){
		unset( $_POST['movie_crew']['nonce'] );
		$crews = $_POST['movie_crew'];

		foreach( $crews as $crew ){
			if( '' != $crew['id'] ){
				$id = $crew['id'];
			}
			elseif( strlen( trim( $crew['name'] ) ) >= 3 ){
				$id = wp_insert_post( array(
					'post_title'	=> trim( $crew['name'] ),
					'post_type'		=> 'profile',
					'post_status'	=> 'draft' ) );
			}

			$wpdb->query( "DELETE FROM `{$wpdb->prefix}movie_cast_n_crew` WHERE `movie`='$movie->ID' AND `type`='crew';");
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}movie_cast_n_crew` (`movie`, `profile`, `role`, `type`)
				VALUES
					($movie->ID, $id, '$crew[role]', 'crew');" );

		}
	}

	if( wp_verify_nonce( $_POST['movie_cast']['nonce'], 'movie_cast' ) ){
		unset( $_POST['movie_cast']['nonce'] );
		$crews = $_POST['movie_cast'];

		foreach( $crews as $cast ){
			if( '' != $cast['id'] ){
				$id = $cast['id'];
			}
			elseif( strlen( trim( $cast['name'] ) ) >= 3 ){
				$id = wp_insert_post( array(
					'post_title'	=> trim( $cast['name'] ),
					'post_type'		=> 'profile',
					'post_status'	=> 'draft' ) );
			}

			$wpdb->query( "DELETE FROM `{$wpdb->prefix}movie_cast_n_crew` WHERE `movie`='$movie->ID' AND `type`='cast';");
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}movie_cast_n_crew` (`movie`, `profile`, `role`, `type`)
				VALUES
					($movie->ID, $id, '$cast[role]', 'cast');" );

		}
	}
}


/**
 * Save meta information related to a profile
 * 
 * @param object $profile Post object of the profile being saved
 */
function mdb_save_profile_meta(){

}


/**
 * Provides a list of profiles of similar name for ajax call.
 * 
 * @param string $term What the title should be like. It will be collected from $_REQUEST if not specified.
 */
function mdb_get_profiles( $term = null ){
	if( null == $term ) $term = $_REQUEST['term'];

	header('Content-type: application/json');

	$posts = get_posts(
		array(
			'post_status'		=> array( 'publish', 'draft' ),
			'post_title_like'	=> trim( $term ),
			'post_type'			=> 'profile',
			'suppress_filters'	=> false ) );

	$profiles = array();
	foreach( $posts as $post ){
		$profile = array();
		$profile['ID']		= $post->ID;
		$profile['label']	= $post->post_title;
		$profile['thumb']	= mdb_get_profile_thumb( $post->ID );
		
		// $profiles[] = $post->post_title;
		$profiles[] = $profile;
	}

	echo json_encode( $profiles );
	exit;
}
add_action( 'wp_ajax_get-profiles', 'mdb_get_profiles' );
add_action( 'wp_ajax_nopriv_get-profiles', 'mdb_get_profiles' );

