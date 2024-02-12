<?php
/*
Plugin Name: Plugin Search V2
Description: Plugin Search est une extension WordPress développée par COM SEE permettant de simplifier la recherche d'entreprises à partir d'une liste donnée.
Version: 1.3.7
Author: Com See
Text Domain: plugin-search
*/

// Fonction pour afficher la page index
function display_index_page() {
    ob_start(); // Capture la sortie HTML

    // Incluez ici le contenu de votre page index.php
    include(plugin_dir_path(__FILE__) . 'index.php');

    // Vérifiez si le formulaire de recherche a été soumis
    if (isset($_POST['search'])) {
        $search_query = sanitize_text_field($_POST['search']);
        
        // Construct the URL with search parameters
        $search_url = site_url('/expert') . '?query=' . urlencode($search_query);
        
        // Redirect the user to the search URL
        echo '<script>window.location.href = "' . $search_url . '";</script>';
        exit();
    }

    $output = ob_get_clean(); // Récupère la sortie HTML capturée
    return $output;
}

// Fonction pour afficher la page search
function display_search_page() {
    ob_start(); // Capture la sortie HTML

    // Enqueue le script Mapbox GL JS ici
    enqueue_mapboxgl_script();

    // Incluez ici le contenu de votre page search.php
    include(plugin_dir_path(__FILE__) . 'search.php');

    $output = ob_get_clean(); // Récupère la sortie HTML capturée
    return $output;
}

function enqueue_mapboxgl_script() {
    wp_enqueue_style('mapbox-style', 'https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css');
    wp_enqueue_script('mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js', array(), null);
}
add_action('wp_enqueue_scripts', 'enqueue_mapboxgl_script');

// Fonction pour le CSS Custom
function enqueue_custom_styles() {
    // Enqueue your CSS file from the 'css' directory of your plugin
    wp_enqueue_style('plugin-search-custom-css', plugins_url('css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

// Ajoutez une action pour afficher la page index sur un shortcode [plugin_search_index]
add_shortcode('plugin_search_index', 'display_index_page');

// Ajoutez une action pour afficher la page search sur un shortcode [plugin_search_search]
add_shortcode('plugin_search_search', 'display_search_page');

// Fonction pour ajouter un menu et un onglet dans le tableau de bord
function plugin_search_menu() {
    add_menu_page(
        'Plugin Search',        // Titre de la page
        'Plugin Search',        // Titre du menu
        'manage_options',       // Capacité requise pour accéder au menu (peut être modifié)
        'plugin-search',        // Identifiant unique de la page
        'plugin_search_page',   // Fonction de rappel pour afficher le contenu de la page
        'dashicons-list-view',  // Icône à afficher à côté du titre du menu (changez-le en fonction de vos besoins)
        10                      // Position du menu dans le tableau de bord
    );

    // Ajouter le sous-menu "Ajouter Entreprise"
    add_submenu_page(
        'plugin-search',            // Identifiant parent de la page
        'Ajouter Entreprise',       // Titre de la page
        'Ajouter Entreprise',       // Titre du sous-menu
        'manage_options',           // Capacité requise pour accéder au sous-menu
        'plugin-search-add',        // Identifiant unique de la page
        'plugin_search_add_page'    // Fonction de rappel pour afficher le contenu de la page d'ajout
    );
}
add_action('admin_menu', 'plugin_search_menu');

// Fonction de rappel pour afficher la page du menu
function plugin_search_page() {
    // Insérez ici le contenu que vous souhaitez afficher dans la page du menu
    echo '<div class="wrap"><h2>Plugin Search</h2>';
    echo '<p>Plugin Search est une extension WordPress développée par COM SEE permettant de simplifier la recherche d\'entreprises à partir d\'une liste donnée.</p>';
    
    // Utilisez plugins_url pour obtenir le lien vers le fichier CSV
    $csv_file_url = plugins_url('entreprises.csv', __FILE__);

    // Ajouter un lien de téléchargement du fichier CSV
    echo '<a class="button-primary" href="' . esc_url($csv_file_url) . '" download="entreprises.csv"><i class="dashicons dashicons-download"></i> Télécharger le fichier CSV</a>';
    
    // Afficher le tableau des entreprises à partir du CSV
    display_enterprises_table();

    echo '</div>';
}
// Fonction pour afficher le tableau des entreprises à partir du CSV
function display_enterprises_table() {
    // Chemin vers le fichier CSV
    $csv_file_path = plugin_dir_path(__FILE__) . 'entreprises.csv';

    // Vérifier si le fichier CSV existe
    if (file_exists($csv_file_path)) {
        echo '<h3>Liste des entreprises</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nom de l\'entreprise</th><th>Membre WEDOOR</th><th>Adresse</th><th>Ville</th><th>Code Postal</th><th>Horaires Semaine</th><th>Horaires WE</th><th>Numéro de téléphone</th><th>Lien du site web</th></tr></thead>';
        echo '<tbody>';

        // Ouvrir le fichier CSV en lecture
        $file = fopen($csv_file_path, 'r');

        // Parcourir les lignes du CSV
        while (($data = fgetcsv($file)) !== false) {
            echo '<tr>';
            foreach ($data as $value) {
                echo '<td>' . esc_html($value) . '</td>';
            }
            echo '</tr>';
        }

        // Fermer le fichier CSV
        fclose($file);

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>Aucun fichier CSV trouvé.</p>';
    }
}

function enqueue_google_fonts() {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap', false);
}

add_action('wp_enqueue_scripts', 'enqueue_google_fonts');

// Fonction de rappel pour afficher la page d'ajout d'entreprise
function plugin_search_add_page() {
    // Vérifiez si le formulaire d'ajout a été soumis
    if (isset($_POST['submit'])) {
        // Traitement du formulaire ici
        $nouveauNom = sanitize_text_field($_POST['nouveau_nom']);
        $nouveauMembre = intval($_POST['nouveau_membre']);
        $nouvelleAdresse = sanitize_text_field($_POST['nouvelle_adresse']);
        $nouvelleVille = sanitize_text_field($_POST['nouvelle_ville']);
        $nouveauCodePostal = sanitize_text_field($_POST['nouveau_code_postal']);
        $horairesSemaine = sanitize_text_field($_POST['horaires_semaine']);
        $horairesWeekend = sanitize_text_field($_POST['horaires_weekend']);
        $numeroTelephone = sanitize_text_field($_POST['numero_telephone']);
        $lienSiteWeb = esc_url($_POST['lien_site_web']);

        // Ouverture du fichier en mode écriture
        $file = fopen(plugin_dir_path(__FILE__) . 'entreprises.csv', 'a');

        if ($file) {
            // Écriture des données dans le fichier CSV
            fputcsv($file, [$nouveauNom, $nouveauMembre, $nouvelleAdresse, $nouvelleVille, $nouveauCodePostal, $horairesSemaine, $horairesWeekend, $numeroTelephone, $lienSiteWeb]);
            fclose($file);
            echo '<div class="updated notice"><p>Entreprise ajoutée avec succès.</p></div>';
        } else {
            echo '<div class="error notice"><p>Erreur lors de l\'ajout de l\'entreprise.</p></div>';
        }
    }
            
    // Affichage du formulaire d'ajout
    echo '<div class="wrap"><h2>Ajouter une entreprise</h2>';
    echo '<form method="post" class="add-company-form">';
        
    // Section 1 : Informations générales
    echo '<div class="form-section">';
    echo '<h3>Informations générales</h3>';

    // Champ Nom de l'entreprise
    echo '<div class="form-group">';
    echo '<label for="nouveau_nom">Nom de l\'entreprise:</label>';
    echo '<input type="text" id="nouveau_nom" name="nouveau_nom" required>';
    echo '</div>';

    // Champ Membre Wedoor (Menu déroulant)
    echo '<div class="form-group">';
    echo '<label for="nouveau_membre">Membre Wedoor:</label>';
    echo '<select id="nouveau_membre" name="nouveau_membre" required>';
    echo '<option value="1">Oui</option>';
    echo '<option value="0">Non</option>';
    echo '</select>';
    echo '</div>';

    // Autres champs de la section 1
    echo '</div>'; // Fin de la section 1

    // Ajouter de l'espace entre les sections
    echo '<div class="form-section-spacer"></div>';

    // Section 2 : Adresse et emplacement
    echo '<div class="form-section">';
    echo '<h3>Adresse et emplacement</h3>';

    // Champ Adresse
    echo '<div class="form-group">';
    echo '<label for="nouvelle_adresse">Adresse:</label>';
    echo '<input type="text" id="nouvelle_adresse" name="nouvelle_adresse" required>';
    echo '</div>';

    // Champ Ville
    echo '<div class="form-group">';
    echo '<label for="nouvelle_ville">Ville:</label>';
    echo '<input type="text" id="nouvelle_ville" name="nouvelle_ville" required>';
    echo '</div>';

    // Champ Code Postal
    echo '<div class="form-group">';
    echo '<label for="nouveau_code_postal">Code Postal:</label>';
    echo '<input type="text" id="nouveau_code_postal" name="nouveau_code_postal" required>';
    echo '</div>';

    // Autres champs de la section 2
    echo '</div>'; // Fin de la section 2

    // Ajouter de l'espace entre les sections
    echo '<div class="form-section-spacer"></div>';

    // Section 3 : Horaires et contact
    echo '<div class="form-section">';
    echo '<h3>Horaires et contact</h3>';

    // Champ Horaires Semaine
    echo '<div class="form-group">';
    echo '<label for="horaires_semaine">Horaires d\'ouverture Semaine:</label>';
    echo '<input type="text" id="horaires_semaine" name="horaires_semaine" required>';
    echo '</div>';

    // Champ Horaires Weekend
    echo '<div class="form-group">';
    echo '<label for="horaires_weekend">Horaires d\'ouverture Weekend:</label>';
    echo '<input type="text" id="horaires_weekend" name="horaires_weekend" required>';
    echo '</div>';

    // Champ Numéro de téléphone
    echo '<div class="form-group">';
    echo '<label for="numero_telephone">Numéro de téléphone:</label>';
    echo '<input type="text" id="numero_telephone" name="numero_telephone" required>';
    echo '</div>';

    // Champ Lien du site web
    echo '<div class="form-group">';
    echo '<label for="lien_site_web">Lien du site web:</label>';
    echo '<input type="text" id="lien_site_web" name="lien_site_web">';
    echo '</div>';

    // Autres champs de la section 3
    echo '</div>'; // Fin de la section 3

    // Bouton pour soumettre le formulaire
    echo '<div class="form-group">';
    echo '<input type="submit" name="submit" class="button-primary" value="Ajouter l\'entreprise">';
    echo '</div>';

    echo '</form></div>';
}