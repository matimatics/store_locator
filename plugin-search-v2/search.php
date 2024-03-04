<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== BOXICONS ===============-->
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="<?php echo plugins_url('css/style.css?ver=7.1.15', __FILE__); ?>">

    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css">

    <title>COM SEE</title>

    <style>
        /* CSS pour agrandir la carte au clic */
        .card.carte-non-wedoor {
            display: flex !important;
            flex-direction: column !important;
            transition: height 0.5s; /* Ajoutez une transition de hauteur de 0.5 seconde */
        }
        .carte-non-wedoor.clicked {
            height: auto !important;
            width: 100% !important; /* Agrandir la largeur à 100% lors du clic */
        }
        .entreprise-info {
            display: none !important;
            padding: 50px !important;
            width: 60% !important;
        }

        .colonnes {
            display: flex !important;
            flex-direction: row !important;
        }

        /* CSS pour afficher les informations sous l'adresse */
        .carte-non-wedoor.clicked .entreprise-info,
        .carte-non-wedoor.clicked .carte-entreprise {
            display: block !important;
            margin-top: 10px !important; /* Espace entre l'adresse et les informations */
        }

        /* Ajoutez du CSS pour la carte Mapbox */
        .map {
            height: 300px;
            width: 500px;
            padding-right: 50px;
        }

        .carte-non-wedoor .data-wedoor {
            margin-top: auto !important;
            margin-bottom: auto !important;
            display: flex !important;
            flex-direction: row !important;
            color: #0B0A0A !important;
            z-index: 1 !important;
        }

        .carte-non-wedoor .web__link {
            font-size: 22px !important;
            color: #d9b448 !important;
            width: 50px !important;
            z-index: 1 !important;
        }

    </style>

</head>
<body>
    <section class="content">
        <div class="search__bar">
            <h1 class="title-plugin-wedoor">Recherche d'entreprises par ville ou code postal</h1>
            <form action="https://wedoor.fr/expert" method="post">
                <p class="label" for="ville">Ville ou code postal de recherche : </p>
                <input class="searchbar" type="text" name="ville" id="ville" placeholder="Entrez une ville ou un code postal" required>
                <input class="button" type="submit" value="Rechercher">

                <p class="label" for="ville"><i class='bx bx-info-circle'></i> La recherche peut durer jusque 10 secondes.</p>
            </form>
        </div>

        <!-- Vous pouvez ajouter ici une barre de chargement ou une icône de chargement -->
        <div class="list" id="liste-entreprises">

        <?php
            // Récupérer la ville ou le code postal de recherche depuis le formulaire (en maintenant la casse)
            $villeRecherche = trim($_POST['ville']);

            // Votre clé API Geonames
            $api_key = "9a999011ee1e49febaf0742625064a38";

            // Fonction pour obtenir les coordonnées géographiques (latitude et longitude) d'une ville
            function getCoordinates($villeRecherche, $api_key)
            {
                $encodedVille = urlencode($villeRecherche);
                $url = "https://api.opencagedata.com/geocode/v1/json?q=$encodedVille&key=$api_key&language=es&pretty=1";
                $url = str_replace(" ", "%20", $url);
                $response = file_get_contents($url);
                $data = json_decode($response, true);

                
                $lat = $data['results'][0]['geometry']['lat'];
                $lng = $data['results'][0]['geometry']['lng'];
                if (isset($data['results'][0]['geometry']['lat']) && isset($data['results'][0]['geometry']['lng'])) {
                    $lat = $data['results'][0]['geometry']['lat'];
                    $lng = $data['results'][0]['geometry']['lng'];
                    return ['lat' => $lat, 'lng' => $lng];
                    } else {
                        return null; // Ville non trouvée
                        }
            }

            function getPostalCode($villeRecherche, $api_key) {

                $data = getCoordinates($villeRecherche, $api_key);
            
                $cord1 = $data['lat'];
                $cord2 = $data['lng'];
                $url = "https://api.opencagedata.com/geocode/v1/json?q=$cord1,$cord2&key=$api_key&language=es&pretty=1";
                $response = file_get_contents($url);
                $json = json_decode($response, true);
                $postCode = $json['results'][0]['components']['postcode'];
                $deuxPremiers = substr($postCode, 0, 2);
                return $deuxPremiers;
            }

            function getFullPostalCode($villeRecherche, $api_key) {
                $data = getCoordinates($villeRecherche, $api_key);
                $cord1 = $data['lat'];
                $cord2 = $data['lng'];
                $url = "https://api.opencagedata.com/geocode/v1/json?q=$cord1,$cord2&key=9a999011ee1e49febaf0742625064a38&language=es&pretty=1";
                $response = file_get_contents($url);
                $json = json_decode($response, true);
                $postCode = $json['results'][0]['components']['postcode'];
                return $postCode;
            }
            
            if(is_numeric($villeRecherche)) {
                $parsedPostalCode = substr($villeRecherche, 0, 2);
                $villeFullPostal = $villeRecherche;
            } else {
                $parsedPostalCode = getPostalCode($villeRecherche, $api_key);
                $villeFullPostal = getFullPostalCode($villeRecherche, $api_key);
            }

            if(isset($_POST['ville']) && !empty($_POST['ville'])){
                // Obtenez le chemin absolu du répertoire racine de WordPress
                $wordpressAbsolutePath = ABSPATH;

                // Définissez le chemin relatif vers le fichier CSV par rapport au répertoire WordPress
                $csvRelativePath = 'wp-content/plugins/plugin-search-v2/entreprises.csv';

                // Concaténez le chemin absolu complet
                $csvAbsolutePath = $wordpressAbsolutePath . $csvRelativePath;

                $entreprises = [];

                // Vérifiez si le code postal fait partie de la liste
                $codes_postaux_fermetures_berger = array(
                    67710, 67310, 67120, 67280, 67190, 67560, 67130, 67870, 67210, 67530, 67570, 67420, 67140, 67150, 67230, 67860, 67680, 67650, 67220, 67730, 67750, 67600, 67820, 67920, 67230, 67390, 68660, 68160, 67880, 67880, 67113
                );

                $codes_postaux_dos_reis = array (
                    88630, 88350, 88300, 88170, 88140, 88320, 88800
                );

                // Extrait les deux premiers chiffres du code postal
                $deux_premiers_chiffres = substr($villeRecherche, 0, 2);

                if(in_array($villeFullPostal, $codes_postaux_fermetures_berger) || ($deux_premiers_chiffres === "39") || in_array($villeFullPostal, $codes_postaux_dos_reis)) {
                    if (in_array($villeFullPostal, $codes_postaux_fermetures_berger)) {
                        echo "<h2 class='city'>Résultat pour : $villeRecherche </h2>";
                        echo "<a href='https://wedoor.fr/expert/porte-de-garage-alsace/' target='_blank'>
                            <div class='card-wedoor bold carte-wedoor'>
                                <i class='web__link bx bx-globe'></i>
                                <p class='data-wedoor'>Fermetures BERGER, Adresse : 2 rue du Krebsbach, 68230 WIHR-AU-VAL</p>
                                <p class='etiquette'>Expert Premium</p>
                            </div>
                        </a>";
                    }

                    if (in_array($villeFullPostal, $codes_postaux_dos_reis)) {
                        echo "<h2 class='city'>Résultat pour : $villeRecherche </h2>";

                        echo "<div class='card carte-non-wedoor' id='dos' onclick='toggleDetailsEntreprise(this)'>
                        <p class='data-wedoor'><i class='web__link bx bx-info-circle'></i> DOS REIS, Adresse : Chp le Roi, 88300 NEUFCHATEAU<p>
                        <div class='colonnes'>
                            <div class='entreprise-info' style='display: none;'> <!-- Masquer les informations par défaut -->
                                <p>Numéro de téléphone : 03 29 94 01 50</p>
                                <p>Horaires Semaine : Lundi - Jeudi : 9h30 à 12h00 / 14h00 à 18h00</p>
                                <p>Horaires Weekend : Vendredi : 9h30 à 12h00</p>                               
                            </div>
                            <div class='map' id='map-dos' style='display: none;'></div>
                        </div>
                    </div>";

                    // Votre code JavaScript pour créer la carte pour cette entreprise
                    echo "<script>
                        mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNjb21zZWUiLCJhIjoiY2xtb3VubGF2MWN1eTJrczVkNTZ1aDhrYiJ9.lvG9yk5bQEsl5ChnQg7jtg';
                        var entrepriseID = 'dos'; // ID de l'entreprise

                        var latitude_dos = 48.366316; // Latitude de l'entreprise
                        var longitude_dos= 5.708697; // Longitude de l'entreprise

                        // Créez une carte Mapbox pour cette entreprise
                        var map_dos = new mapboxgl.Map({
                            container: 'map-' + entrepriseID, // Utilisez l'identifiant unique de la carte
                            style: 'mapbox://styles/mapbox/streets-v11', // Choisissez un style de carte
                            center: [longitude_dos, latitude_dos], // Coordonnées de l'entreprise
                            zoom: 12 // Ajustez le niveau de zoom selon vos besoins
                        });

                        // Ajoutez un marqueur pour l'emplacement de l'entreprise
                        new mapboxgl.Marker()
                            .setLngLat([longitude_dos, latitude_dos]) // Coordonnées de l'entreprise
                            .addTo(map_dos);
                    </script>";
                    }
                  

                    // Vérifiez si les deux premiers chiffres correspondent au département 39 (Jura)
                    if ($deux_premiers_chiffres === "39") {
                        echo "<h2 class='city'>Résultat pour : $villeRecherche </h2>";
                        echo "<a href='#'>
                            <div class='card-wedoor bold carte-wedoor'>
                                <p class='data-wedoor'>JURA FERMETURES, Adresse : 465 Rue de la Lième, 39570 PERRIGNY</p>
                                <p class='etiquette'>03 84 47 38 61</p>
                            </div>
                        </a>";
                        
                        echo "<a href='#'>
                            <div class='card-wedoor bold carte-wedoor'>
                                <p class='data-wedoor'>JURA FERMETURES, Adresse : 493 Av. de Lattre de Tassigny, 39300 Champagnole</p>
                                <p class='etiquette'>03 84 35 58 99</p>
                            </div>
                        </a>";
                    }
                }else {
                    $file = fopen($csvAbsolutePath, 'r');

                    if ($file) {
                        fgetcsv($file); // Ignorer la première ligne (en-têtes de colonnes)

                        while (($data = fgetcsv($file, 1000, ',')) !== false) {
                            $nom = trim($data[0]);
                            $membreWedoor = intval(trim($data[1]));
                            $adresse = trim($data[2]);
                            $ville = trim($data[3]);
                            $codePostal = trim($data[4]);
                            $horairesSemaine = trim($data[5]);
                            $horairesWE = trim($data[6]);
                            $numTelephone = trim($data[7]);
                            $lienSiteWeb = trim($data[8]);

                            $regionArea = substr($codePostal, 0 , 2);

                            if ($parsedPostalCode === $regionArea) {
                                $entreprises[] = [
                                    'nom' => $nom,
                                    'membre_wedoor' => $membreWedoor,
                                    'adresse' => $adresse,
                                    'ville' => $ville,
                                    'code_postal' => $codePostal,
                                    'lien_site_web' => $lienSiteWeb,
                                    'num_telephone' => $numTelephone,
                                    'horaires_semaine' => $horairesSemaine,
                                    'horaires_weekend' => $horairesWE 
                                ];
                            }
                        }

                        fclose($file);
                    } 

                     // Trier les entreprises par leur appartenance à WEDOOR (1 en premier)
                     usort($entreprises, function ($a, $b) {
                        // Membre wedoor
                        $result = $b['membre_wedoor'] - $a['membre_wedoor'];
    
                        // PostCode sort
                        if ($result == 0) {
                            $result = strcmp($a['code_postal'], $b['code_postal']);
                        }
    
                        return $result;
                    });
                
                    if (empty($entreprises)) {
                        echo "<h2 class='city'>Contacter WEDOOR : </h2>";
                        echo "<div class='entreprise-list'>";
                            echo "<div class='card carte-non-wedoor' id='wedoor' onclick='toggleDetailsEntreprise(this)'>
                            <p class='data-wedoor'><i class='web__link bx bx-info-circle'></i> WEDOOR, Adresse : 1 Rue du Climont, 67220 TRIEMBACH-AU-VAL<p>
                            <div class='colonnes'>
                                <div class='entreprise-info' style='display: none;'> <!-- Masquer les informations par défaut -->
                                    <p>Numéro de téléphone : 03 90 57 99 32</p>
                                    <p>Horaires Semaine : Lundi - Jeudi : de 08h30 à 12h00 / de 14h00 à 16h30</p>
                                    <p>Horaires Weekend : Vendredi : de 8h30 à 12h00</p>                               
                                </div>
                                <div class='map' id='map-wedoor' style='display: none;'></div>
                            </div>
                        </div>";

                        echo "<script>
                            mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNjb21zZWUiLCJhIjoiY2xtb3VubGF2MWN1eTJrczVkNTZ1aDhrYiJ9.lvG9yk5bQEsl5ChnQg7jtg';
                            var entrepriseID = 'wedoor'; // ID de l'entreprise

                            var latitude_mrg_habitat = 48.3374026; // Latitude de l'entreprise
                            var longitude_mrg_habitat = 7.3163588; // Longitude de l'entreprise

                            // Créez une carte Mapbox pour cette entreprise
                            var map_mrg_habitat = new mapboxgl.Map({
                                container: 'map-' + entrepriseID, // Utilisez l'identifiant unique de la carte
                                style: 'mapbox://styles/mapbox/streets-v11', // Choisissez un style de carte
                                center: [longitude_mrg_habitat, latitude_mrg_habitat], // Coordonnées de l'entreprise
                                zoom: 12 // Ajustez le niveau de zoom selon vos besoins
                            });

                            // Ajoutez un marqueur pour l'emplacement de l'entreprise
                            new mapboxgl.Marker()
                                .setLngLat([longitude_mrg_habitat, latitude_mrg_habitat]) // Coordonnées de l'entreprise
                                .addTo(map_mrg_habitat);
                        </script>";
                    } 
                    else {
                        echo "<h2 class='city'>Résultats de la recherche pour la ville ou le code postal : $villeRecherche</h2>";
                        echo "<div class='entreprise-list'>";

                        //Exception pour MRG HABITAT
                        if($villeRecherche == "01710"){
                            echo "<a href='https://wedoor.fr/expert/porte-de-garage-pays-de-gex/' target='_blank'>
                                <div class='card-wedoor bold carte-wedoor'>
                                    <i class='web__link bx bx-globe'></i>
                                    <p class='data-wedoor'>MRG HABITAT, Adresse : 13 Chemin de la Praille, 01710 THOIRY</p> 
                                    <p class='etiquette'>Expert Premium</p>
                                </div>
                            </a>";
                        }

                        foreach ($entreprises as $entreprise) {
                            $lienSiteWeb = ''; // Lien par défaut

                            // Vérifier si le lien du site web est présent dans le CSV
                            if (!empty($entreprise['lien_site_web'])) {
                                $lienSiteWeb = $entreprise['lien_site_web'];
                            }

                            // Utiliser le nom de l'entreprise comme attribut id
                            $entrepriseID = strtolower(str_replace(' ', '_', $entreprise['nom']));

                            if ($entreprise['membre_wedoor'] == 1) {
                                echo "<a href='$lienSiteWeb' target='_blank'>
                                        <div class='card-wedoor bold carte-wedoor'>
                                            <i class='web__link bx bx-globe'></i>
                                            <p class='data-wedoor'>{$entreprise['nom']}, Adresse : {$entreprise['adresse']}, {$entreprise['code_postal']} {$entreprise['ville']}</p> 
                                            <p class='etiquette'>Expert Premium</p>
                                        </div>
                                    </a>";

                                    // Votre code JavaScript pour créer la carte pour cette entreprise
                                    echo "<script>
                                    mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNjb21zZWUiLCJhIjoiY2xtb3VubGF2MWN1eTJrczVkNTZ1aDhrYiJ9.lvG9yk5bQEsl5ChnQg7jtg';
                                    var entrepriseID = '$entrepriseID'; // ID de l'entreprise

                                    var latitude_$entrepriseID = $latitude; // Latitude de l'entreprise
                                    var longitude_$entrepriseID = $longitude; // Longitude de l'entreprise

                                    // Créez une carte Mapbox pour cette entreprise
                                    var map_$entrepriseID = new mapboxgl.Map({
                                        container: 'map-' + entrepriseID, // Utilisez l'identifiant unique de la carte
                                        style: 'mapbox://styles/mapbox/streets-v11', // Choisissez un style de carte
                                        center: [longitude_$entrepriseID, latitude_$entrepriseID], // Coordonnées de l'entreprise
                                        zoom: 12 // Ajustez le niveau de zoom selon vos besoins
                                    });

                                    // Ajoutez un marqueur pour l'emplacement de l'entreprise
                                    new mapboxgl.Marker()
                                        .setLngLat([longitude_$entrepriseID, latitude_$entrepriseID]) // Coordonnées de l'entreprise
                                        .addTo(map_$entrepriseID);
                                </script>";
                            } else {
                                //on récup les coordonées 
                                $coordinates = getCoordinates($entreprise['ville'], $api_key);

                                if ($coordinates !== null) {
                                    $latitude = $coordinates['lat'];
                                    $longitude = $coordinates['lng'];
                        
                                    echo "<div class='card carte-non-wedoor' id='$entrepriseID' onclick='toggleDetailsEntreprise(this)'>
                                        <p class='data-wedoor'><i class='web__link bx bx-info-circle'></i> {$entreprise['nom']}, Adresse : {$entreprise['adresse']}, {$entreprise['code_postal']} {$entreprise['ville']}<p>
                                        <div class='colonnes'>
                                            <div class='entreprise-info' style='display: none;'> <!-- Masquer les informations par défaut -->
                                                <p>Numéro de téléphone : {$entreprise['num_telephone']}</p>
                                                <p>Horaires Semaine : {$entreprise['horaires_semaine']}</p>
                                                <p>Horaires Weekend : {$entreprise['horaires_weekend']}</p>                               
                                            </div>

                                            <div class='map' id='map-$entrepriseID' style='display: none;'></div>
                                        </div>
                                    </div>";

                                    // Votre code JavaScript pour créer la carte pour cette entreprise
                                    echo "<script>
                                        mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNjb21zZWUiLCJhIjoiY2xtb3VubGF2MWN1eTJrczVkNTZ1aDhrYiJ9.lvG9yk5bQEsl5ChnQg7jtg';
                                        var entrepriseID = '$entrepriseID'; // ID de l'entreprise

                                        var latitude_$entrepriseID = $latitude; // Latitude de l'entreprise
                                        var longitude_$entrepriseID = $longitude; // Longitude de l'entreprise

                                        // Créez une carte Mapbox pour cette entreprise
                                        var map_$entrepriseID = new mapboxgl.Map({
                                            container: 'map-' + entrepriseID, // Utilisez l'identifiant unique de la carte
                                            style: 'mapbox://styles/mapbox/streets-v11', // Choisissez un style de carte
                                            center: [longitude_$entrepriseID, latitude_$entrepriseID], // Coordonnées de l'entreprise
                                            zoom: 12 // Ajustez le niveau de zoom selon vos besoins
                                        });

                                        // Ajoutez un marqueur pour l'emplacement de l'entreprise
                                        new mapboxgl.Marker()
                                            .setLngLat([longitude_$entrepriseID, latitude_$entrepriseID]) // Coordonnées de l'entreprise
                                            .addTo(map_$entrepriseID);
                                    </script>";

                                    echo "<script>
                                        // Récupérez l'élément de la section de liste par son ID
                                        var listeEntreprises = document.getElementById('liste-entreprises');
                
                                        // Faites défiler la vue jusqu'à la section de liste
                                        listeEntreprises.scrollIntoView({ behavior: 'smooth' });
                                    </script>";
                                } 

                                if($villeRecherche == "01710"){
                                    echo "<div class='card carte-non-wedoor' id='mrg_habitat' onclick='toggleDetailsEntreprise(this)'>
                                    <p class='data-wedoor'><i class='web__link bx bx-info-circle'></i> MRG Habitat, Adresse : 13 Chemin de la Praille, 01710 THOIRY<p>
                                    <div class='colonnes'>
                                        <div class='entreprise-info' style='display: none;'> <!-- Masquer les informations par défaut -->
                                            <p>Numéro de téléphone : 04 50 59 85 35</p>
                                            <p>Horaires Semaine : Lundi - Vendredi : de 08h00 à 17h00</p>
                                            <p>Horaires Weekend : </p>                               
                                        </div>
                                        <div class='map' id='map-mrg_habitat' style='display: none;'></div>
                                    </div>
                                </div>";

                                // Votre code JavaScript pour créer la carte pour cette entreprise
                                echo "<script>
                                    mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNjb21zZWUiLCJhIjoiY2xtb3VubGF2MWN1eTJrczVkNTZ1aDhrYiJ9.lvG9yk5bQEsl5ChnQg7jtg';
                                    var entrepriseID = 'mrg_habitat'; // ID de l'entreprise

                                    var latitude_mrg_habitat = 46.2333; // Latitude de l'entreprise
                                    var longitude_mrg_habitat = 5.9667; // Longitude de l'entreprise

                                    // Créez une carte Mapbox pour cette entreprise
                                    var map_mrg_habitat = new mapboxgl.Map({
                                        container: 'map-' + entrepriseID, // Utilisez l'identifiant unique de la carte
                                        style: 'mapbox://styles/mapbox/streets-v11', // Choisissez un style de carte
                                        center: [longitude_mrg_habitat, latitude_mrg_habitat], // Coordonnées de l'entreprise
                                        zoom: 12 // Ajustez le niveau de zoom selon vos besoins
                                    });

                                    // Ajoutez un marqueur pour l'emplacement de l'entreprise
                                    new mapboxgl.Marker()
                                        .setLngLat([longitude_mrg_habitat, latitude_mrg_habitat]) // Coordonnées de l'entreprise
                                        .addTo(map_mrg_habitat);
                                </script>";
                                }

                                echo "<script>
                                    // Récupérez l'élément de la section de liste par son ID
                                    var listeEntreprises = document.getElementById('liste-entreprises');
            
                                    // Faites défiler la vue jusqu'à la section de liste
                                    listeEntreprises.scrollIntoView({ behavior: 'smooth' });
                                </script>";
                            }
                        }
                        echo "</div>"; // Fermez la div pour la liste des entreprises
                    }
                
                }
            }
            ?>
        </div>
    </section>

    <script>
        function toggleDetailsEntreprise(element) {
            // Récupérer l'élément de l'entreprise par son ID
            var entrepriseElement = element;

            // Récupérer les informations de l'entreprise et la carte
            var entrepriseInfo = entrepriseElement.querySelector(".entreprise-info");
            var map = entrepriseElement.querySelector(".map");

            // Vérifier si les informations de l'entreprise sont visibles
            if (entrepriseInfo.style.display === "block") {
                // Les informations sont visibles, donc les masquer
                entrepriseInfo.style.display = "none";
                map.style.display = "none";
                entrepriseElement.classList.remove("clicked");
            } else {
                // Les informations ne sont pas visibles, donc les afficher
                entrepriseInfo.style.display = "block";
                map.style.display = "block";
                entrepriseElement.classList.add("clicked");
            }
        }
    </script>

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js'></script>

</body>
</html>