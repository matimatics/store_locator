<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== BOXICONS ===============-->
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css">

    <title>COM SEE</title>

    <style>
        /* CSS pour agrandir la carte au clic */
        .card.carte-non-wedoor {
            display: flex !important;
            flex-direction: column !important;
            transition: height 0.5s;
            /* Ajoutez une transition de hauteur de 0.5 seconde */
        }

        .carte-non-wedoor.clicked {
            height: auto !important;
            width: 100% !important;
            /* Agrandir la largeur à 100% lors du clic */
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
            margin-top: 10px !important;
            /* Espace entre l'adresse et les informations */
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
            color: #ffe200 !important;
            width: 50px !important;
            z-index: 1 !important;
        }
    </style>
</head>

<body>
    <section class="content">
        <div class="search__bar">
            <h1>Trouver un expert proche de chez vous</h1>
            <form action="test.php" method="post">
                <p class="label" for="ville">Recherche par ville ou code postal : </p>
                <input class="searchbar" type="text" name="ville" id="ville" placeholder="Entrez une ville ou un code postal" required>
                <input class="button" type="submit" value="Rechercher">
            </form>
        </div>
        <div class="list" id="liste-entreprises">

            <?php
            // Récupérer la ville ou le code postal de recherche depuis le formulaire (en maintenant la casse)
            //$villeRecherche = trim($_POST['ville']);
            $villeRecherche = "nancy";
            // Votre clé API Geonames
            //$api_key = "lucascomsee";

            $API_KEY_OC = "9a999011ee1e49febaf0742625064a38";
            // Fonction pour obtenir les coordonnées géographiques (latitude et longitude) d'une ville

            if ($villeRecherche === "01710" || strtolower($villeRecherche) === "thoiry") {
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
            } else {

                function getCoordinates($villeRecherche, $API_KEY_OC)
                {
                    var_dump("ENCODED VILLE EN GETCOORDINATES: " . $villeRecherche);
                    $encodedVille = urlencode($villeRecherche);
                    var_dump($encodedVille);
                    $url = "https://api.opencagedata.com/geocode/v1/json?q=$encodedVille&key=$API_KEY_OC&language=es&pretty=1";
                    $url = str_replace(" ", "%20", $url);
                    var_dump("URL PETICION: " . $url);
                    $response = file_get_contents($url);
                    $data = json_decode($response, true);


                    $lat = $data['results'][0]['geometry']['lat'];
                    $lng = $data['results'][0]['geometry']['lng'];
                    if (isset($data['results'][0]['geometry']['lat']) && isset($data['results'][0]['geometry']['lng'])) {
                        $lat = $data['results'][0]['geometry']['lat'];
                        $lng = $data['results'][0]['geometry']['lng'];
                        /* $lat = $data['geonames'][0]['lat'];
                        var_dump("LAT line 113: ".$lat);
                        $lng = $data['geonames'][0]['lng'];
                        var_dump("LNG line 113: ".$lng); */
                        return ['lat' => $lat, 'lng' => $lng];
                    } else {
                        return null; // Ville non trouvée
                    }
                }

                function getPostalCode($villeRecherche, $API_KEY_OC)
                {

                    $data = getCoordinates($villeRecherche, $API_KEY_OC);

                    $cord1 = $data['lat'];
                    $cord2 = $data['lng'];
                    $url = "https://api.opencagedata.com/geocode/v1/json?q=$cord1,$cord2&key=$API_KEY_OC&language=es&pretty=1";
                    var_dump("URL CON COORDENADAS: " . $url);
                    $response = file_get_contents($url);
                    $json = json_decode($response, true);
                    $postCode = $json['results'][0]['components']['postcode'];
                    var_dump("POSTCODE line 116: " . $postCode);
                    $deuxPremiers = substr($postCode, 0, 2);
                    var_dump("2 PREMIERS CHIFFRES: " . $deuxPremiers);
                    return $deuxPremiers;
                }

                if (is_numeric($villeRecherche)) {
                    $parsedPostalCode = substr($villeRecherche, 0, 2);
                } else {
                    $parsedPostalCode = getPostalCode($villeRecherche, $API_KEY_OC);
                }

                var_dump("PARSEDPOSTALCODE " . $parsedPostalCode);

                if (isset($villeRecherche) && !empty($villeRecherche)) {
                    var_dump("VILLE RECHERCHE " . $villeRecherche);
                    // Définissez le chemin relatif vers le fichier CSV par rapport au répertoire WordPress
                    $csvRelativePath = 'entreprises.csv';

                    // Concaténez le chemin absolu complet
                    $csvAbsolutePath = $csvRelativePath;
                    $entreprises = [];

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
                            $regionArea = substr($codePostal, 0, 2);
                            /*  // Calculer la distance entre la ville ou le code postal de recherche et la ville de l'entreprise
                        $distance = calculateDistance($villeRecherche, $ville, $api_key);

                        // Si la distance est inférieure ou égale à 50 km, ajouter l'entreprise à la liste
                        if ($distance !== null && $distance <= 30) {
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
                            ]; */
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
                    }

                    fclose($file);
                } else {
                    echo "Impossible d'ouvrir le fichier entreprises.csv.";
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

                // Afficher la liste des entreprises trouvées
                if (empty($entreprises)) {
                    echo "<h2 class='city'>Contacter WEDOOR : </h2>";
                    echo "<div class='entreprise-list'>";
                    echo "<div class='card carte-non-wedoor' id='wedoor' onclick='toggleDetailsEntreprise(this)'>
                        <p class='data-wedoor'><i class='web__link bx bx-info-circle'></i> WEDOOR, Adresse : 1 Rue du Climont, 67220 Triembach-au-Val<p>
                        <div class='colonnes'>
                            <div class='entreprise-info' style='display: none;'> <!-- Masquer les informations par défaut -->
                                <p>Numéro de téléphone : 03 90 57 99 32</p>
                                <p>Horaires Semaine : Lundi - Jeudi : de 08h30 à 12h00 / de 14h00 à 16h30</p>
                                <p>Horaires Weekend : Vendredi : de 8h30 à 12h00</p>                               
                            </div>
                            <div class='map' id='map-wedoor' style='display: none;'></div>
                        </div>
                    </div>";
                } else {
                    echo "<h2 class='city'>Résultats de la recherche pour la ville ou le code postal : $villeRecherche</h2>";
                    echo "<div class='entreprise-list'>";
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
                            echo "</div>";
                        } else {

                            //on récup les coordonées 
                            $coordinates = getCoordinates($entreprise['ville'], $API_KEY_OC);

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
                            }

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
                    }

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
                echo "</div>"; // Fermez la div pour la liste des entreprises
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