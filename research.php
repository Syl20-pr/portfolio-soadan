<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/page.php';

$LANG = 'fr';
$PAGE = 'research';
$BASE = '';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">Recherche &amp; publications</p>
        <h1>Construire une voix analytique africaine.</h1>
        <p class="lead">Cette page présente mes axes de recherche, mes publications disponibles et les travaux en cours
          de développement. Les publications non encore publiées sont signalées comme telles.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Axes de recherche</p>
          <h2 class="section-title">Domaines d’intérêt.</h2>
        </div>
        <div class="grid-3">
          <article class="card green">
            <h3>Gouvernance de l’IA en Afrique</h3>
            <p>Cadres d’audit, passeports algorithmiques et alignement sur la stratégie continentale de l’Union
              africaine.</p>
          </article>
          <article class="card green">
            <h3>Souveraineté numérique</h3>
            <p>Législations nationales, hébergement des données critiques et autonomie d’infrastructure.</p>
          </article>
          <article class="card green">
            <h3>Gouvernance des données</h3>
            <p>Protection des données personnelles, flux transfrontaliers et Convention de Malabo.</p>
          </article>
          <article class="card gold">
            <h3>Politiques de cybersécurité</h3>
            <p>Protection des infrastructures critiques, attribution et coopération régionale.</p>
          </article>
          <article class="card gold">
            <h3>Infrastructures publiques numériques</h3>
            <p>Interopérabilité, identité, santé et services publics numériques.</p>
          </article>
          <article class="card gold">
            <h3>Diplomatie technologique</h3>
            <p>Négociations de traités, standards mondiaux et représentation africaine.</p>
          </article>
          <article class="card">
            <h3>Transformation numérique</h3>
            <p>Administration publique, capacités locales et adoption technologique.</p>
          </article>
          <article class="card">
            <h3>IA &amp; administration publique</h3>
            <p>Décision automatisée, transparence et responsabilité institutionnelle.</p>
          </article>
          <article class="card">
            <h3>Technologie &amp; développement africain</h3>
            <p>Innovation locale, biens publics numériques et développement.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-soft">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Publications</p>
          <h2 class="section-title">Notes d’analyse disponibles.</h2>
        </div>
        <div class="grid-2">
          <article class="card gold">
            <p class="project-domain">Policy Brief #01</p>
            <h3>Gouvernance de l’IA en Afrique : de la dépendance à la souveraineté stratégique</h3>
            <p>Analyse de la stratégie continentale sur l’IA et propositions pour un cadre d’audit algorithmique
              indépendant.</p>
            <p class="mb-0"><a class="link-inline" href="publications/policy-brief-01.php">Lire la note →</a></p>
          </article>
          <article class="card gold">
            <p class="project-domain">Policy Brief #02</p>
            <h3>La cybersécurité comme enjeu de politique étrangère en Afrique</h3>
            <p>De la Convention de Malabo à la résilience régionale collective : cybersécurité, attribution et
              diplomatie.</p>
            <p class="mb-0"><a class="link-inline" href="publications/policy-brief-02.php">Lire la note →</a></p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Travaux en cours</p>
          <h2 class="section-title">Recherche en développement.</h2>
        </div>
        <div class="table-wrap">
          <table class="data">
            <caption>Travaux annoncés, non encore publiés.</caption>
            <thead>
              <tr>
                <th scope="col">Titre</th>
                <th scope="col">Statut</th>
                <th scope="col">Thème</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Infrastructures publiques numériques &amp; droits fondamentaux</td>
                <td><strong>Research in Development</strong></td>
                <td>Registres de population, interopérabilité sanitaire FHIR et protection constitutionnelle des données
                  biométriques.</td>
              </tr>
              <tr>
                <td>Données critiques et hébergement souverain dans l’espace ZLECAf</td>
                <td><strong>Research in Development</strong></td>
                <td>Flux transfrontaliers, cloud souverain et autonomie technologique.</td>
              </tr>
              <tr>
                <td>Diplomatie numérique africaine et négociation des traités technologiques</td>
                <td><strong>Upcoming Publication</strong></td>
                <td>Représentation africaine dans les enceintes multilatérales et standards mondiaux.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="mt-4 mb-0"><a class="btn btn-secondary" href="contact.php">Discuter d’une collaboration de
            recherche</a></p>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
