<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/page.php';

$LANG = 'fr';
$PAGE = 'projects';
$BASE = '';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">Projets</p>
        <h1>Construire, documenter, gouverner.</h1>
        <p class="lead">Six initiatives reliant technologie, impact public et questions de gouvernance. Chaque projet
          affiche son stade réel et les éléments documentés disponibles.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="project-list">
          <article class="project-row">
            <div>
              <p class="project-domain">Gouvernance de l’IA · Infrastructure de confiance</p>
              <h3>BITC — Blue Intelligence Technologies Corporation</h3>
              <p class="project-desc">Cadre conceptuel et technique pour auditer, documenter et gouverner les systèmes
                d’IA déployés dans l’administration : « AI Passport », évaluation des biais statistiques (DIR) et
                conformité à la stratégie de l’Union africaine.</p>
              <div class="tag-row">
                <span class="status status-prototype">Prototype</span>
                <span class="tag">AI Governance</span><span class="tag">Audit algorithmique</span><span
                  class="tag">Conformité UA</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/bitc.php">Étude de cas complète →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Santé · Infrastructure publique numérique</p>
              <h3>African Health OS</h3>
              <p class="project-desc">Architecture d’une infrastructure publique numérique de santé reliant patients,
                centres hospitaliers, pharmacies et autorités autour de standards ouverts (HL7/FHIR) et d’un registre
                cryptographique anti-falsification.</p>
              <div class="tag-row">
                <span class="status status-concept">Concept</span>
                <span class="tag">HL7/FHIR R4</span><span class="tag">DPI santé</span><span class="tag">Souveraineté des
                  données</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/african-health-os.php">Étude de cas complète
                  →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Civic tech · Participation citoyenne</p>
              <h3>4 Voix Jeunesse Togo</h3>
              <p class="project-desc">Plateforme civique géoréférencée permettant aux citoyens de documenter les
                défaillances urbaines, avec floutage spatial différentiel (Geohash) et validation décentralisée par les
                pairs.</p>
              <div class="tag-row">
                <span class="status status-concept">Concept</span>
                <span class="tag">Civic Tech</span><span class="tag">Confidentialité spatiale</span><span
                  class="tag">Gouvernance locale</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/4-voix-jeunesse.php">Étude de cas complète
                  →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Média · Débat public</p>
              <h3>Tribune de la Jeunesse Togolaise — TJT</h3>
              <p class="project-desc">Format d’émission et de podcast indépendant dédié aux questions de société, de
                justice, de gouvernance et de jeunesse, adossé à une charte éditoriale de neutralité et à une
                méthodologie de vérification contradictoire.</p>
              <div class="tag-row">
                <span class="status status-development">Développement</span>
                <span class="tag">Débat public</span><span class="tag">Fact-checking</span><span class="tag">État de
                  droit</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/tjt.php">Étude de cas complète →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Diplomatie numérique · Multilatéralisme</p>
              <h3>Jeunesse &amp; Diplomatie Africaine — JDA</h3>
              <p class="project-desc">Initiative autour de la jeunesse, de la diplomatie et des affaires africaines :
                préparation des jeunes professionnels à la diplomatie technique, à la négociation des traités et aux
                délibérations multilatérales.</p>
              <div class="tag-row">
                <span class="status status-research">Recherche</span>
                <span class="tag">Tech Diplomacy</span><span class="tag">Modèle UA</span><span
                  class="tag">Traités</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/jda.php">Étude de cas complète →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Recherche · Analyse comparative</p>
              <h3>African Digital Sovereignty Observatory</h3>
              <p class="project-desc">Projet de recherche et d’analyse sur la souveraineté numérique africaine :
                cartographie des législations nationales, des infrastructures cloud souveraines et des flux de données
                transfrontaliers.</p>
              <div class="tag-row">
                <span class="status status-research">Recherche</span>
                <span class="tag">55 États UA</span><span class="tag">Convention de Malabo</span><span
                  class="tag">Indice composite</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/digital-sovereignty-observatory.php">Étude de
                  cas complète →</a></p>
            </div>
          </article>
        </div>
        <p class="mt-4 note">Aucun de ces projets n’est présenté comme déployé. Les stades indiqués — concept,
          recherche, prototype, développement — reflètent l’avancement réel documenté à ce jour.</p>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
