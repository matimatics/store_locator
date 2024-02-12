<!DOCTYPE html>
<html>
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== BOXICONS ===============-->
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="<?php echo plugins_url('css/style.css?ver=7.1.15', __FILE__); ?>">

    <title>COM SEE</title>
</head>
<body>
    <section class="content">
        <div class="search__bar">
            <h1>Recherche d'entreprises par ville ou code postal</h1>
            <form action="https://wedoor.fr/expert" method="post">
                <p class="label" for="ville">Ville ou code postal de recherche : </p>
                <input class="searchbar" type="text" name="ville" id="ville" placeholder="Entrez une ville ou un code postal" required>
                <input class="button" type="submit" value="Rechercher">
            </form>
        </div>
    </section>
</body>
</html>
