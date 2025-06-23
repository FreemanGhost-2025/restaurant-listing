<?php
/*
Plugin Name: Restaurant Listing with Filter
Plugin URI: https://github.com/FreemanGhost-2025/restaurant-listing
Description: Affiche une liste de restaurants avec filtres personnalisés.
Version: 1.0.0
Author: Freeman Ghost
Author URI: https://github.com/FreemanGhost-2025
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

GitHub Plugin URI: https://github.com/FreemanGhost-2025/restaurant-listing
GitHub Branch: main
*/

function rl_register_restaurant_cpt() {
    $labels = array(
        'name' => 'Restaurants',
        'singular_name' => 'Restaurant',
        'menu_name' => 'Restaurants',
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'restaurants'),
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-store'
    );
    register_post_type('restaurant', $args);
}
add_action('init', 'rl_register_restaurant_cpt');

function rl_afficher_liste_restaurants() {
    echo '<form method="GET" class="restaurant-filter">';
    echo '<input type="text" name="type" placeholder="Type de cuisine" value="' . esc_attr($_GET['type'] ?? '') . '"/>';
    echo '<input type="number" name="prix_max" placeholder="Prix max" value="' . esc_attr($_GET['prix_max'] ?? '') . '"/>';
    echo '<input type="number" name="etoiles" min="1" max="5" placeholder="Étoiles" value="' . esc_attr($_GET['etoiles'] ?? '') . '"/>';
    echo '<button type="submit">Filtrer</button>';
    echo '</form>';

    $args = array(
        'post_type' => 'restaurant',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $meta_query = array('relation' => 'AND');

    if (!empty($_GET['type'])) {
        $meta_query[] = array(
            'key' => 'type_de_cuisine',
            'value' => sanitize_text_field($_GET['type']),
            'compare' => 'LIKE'
        );
    }
    if (!empty($_GET['prix_max'])) {
        $meta_query[] = array(
            'key' => 'montant_a_prevoir',
            'value' => intval($_GET['prix_max']),
            'type' => 'NUMERIC',
            'compare' => '<='
        );
    }
    if (!empty($_GET['etoiles'])) {
        $meta_query[] = array(
            'key' => 'nombre_etoiles',
            'value' => intval($_GET['etoiles']),
            'type' => 'NUMERIC',
            'compare' => '='
        );
    }

    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query($args);
    ob_start();
    if ($query->have_posts()) {
        echo '<div class="liste-restaurants">';
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $image = get_field('image_restaurant', $post_id);
            $nom = get_field('nom_du_restaurant', $post_id);
            $type = get_field('type_de_cuisine', $post_id);
            $adresse = get_field('adresse', $post_id);
            $description = get_field('description', $post_id);
            $telephone = get_field('numero_telephone', $post_id);
            $montant = get_field('montant_a_prevoir', $post_id);
            $etoiles = get_field('nombre_etoiles', $post_id);
            $lien_reservation = get_field('lien_reservation', $post_id);

            echo '<div class="restaurant-card">';
                echo '<div class="restaurant-left">';
                    if (!empty($image) && is_array($image)) {
                        echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($nom) . '" class="restaurant-image" />';
                    }
                    echo '<div class="restaurant-info">';
                        echo '<h3 class="restaurant-title">' . esc_html($nom) . '</h3>';
                        if ($type) echo '<p class="restaurant-type"><i class="fa-solid fa-utensils"></i> ' . esc_html($type) . '</p>';
                        if ($adresse) echo '<p class="restaurant-adresse"><i class="fa-solid fa-location-dot"></i> ' . esc_html($adresse) . '</p>';
                        if ($description) echo '<p class="restaurant-description">' . esc_html($description) . '</p>';
			
			echo '<div class="restaurant-contact-note">';
  				if ($telephone) echo '<span class="restaurant-telephone"><i class="fa-solid fa-phone-volume"></i> ' . esc_html($telephone) . '</span>';
  				if ($etoiles) echo '<span class="restaurant-etoiles">⭐ ' . esc_html($etoiles) . ' étoiles</span>';
			echo '</div>';

			
			
                    echo '</div>';
                echo '</div>';
		   echo '<div class="restaurant-divider-vertical"></div>';
                echo '<div class="restaurant-right">';
                    if ($montant) {
						echo '<span class="price-label">Montant à prévoir</span>';
                        echo '<p class="restaurant-price">' . esc_html($montant) . ' FCFA</p>';
                        
                    }
                    if ($lien_reservation) {
                        echo '<a href="' . esc_url($lien_reservation) . '" class="reserve-button" target="_blank">Réserver</a>';
                    }
                echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>Aucun restaurant trouvé.</p>';
    }
    return ob_get_clean();
}
add_shortcode('liste_restaurants', 'rl_afficher_liste_restaurants');

function rl_enqueue_styles() {
    wp_enqueue_style('restaurant-listing-style-filter', plugin_dir_url(__FILE__) . 'assets/style.css');
}
add_action('wp_enqueue_scripts', 'rl_enqueue_styles');
