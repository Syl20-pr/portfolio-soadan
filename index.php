<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/page.php';

$LANG = 'fr';
$PAGE = 'index';
$BASE = '';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/includes/header.php';
?>


<main id="main">

  <section class="hero">
    <div class="container hero-grid">
      <div>
        <p class="eyebrow">Lomé, Togo — Afrique de l’Ouest</p>
        <h1 class="hero-title">SOADAN Koffi<br><span>Sylvain</span></h1>
        <p class="hero-lead"><strong>African Digital Governance &amp; AI Policy.</strong> Un parcours mené à
          l’intersection de l’ingénierie logicielle, du droit public, des politiques publiques et des relations
          internationales.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="projects.php">Explore My Work</a>
          <a class="btn btn-secondary" href="dossier.php">Professional Dossier</a>
        </div>
        <div class="hero-meta">
          <span>Technology</span>
          <span>Law</span>
          <span>Public Policy</span>
          <span>International Affairs</span>
        </div>
      </div>
      <figure class="hero-portrait">
        <img src="assets/images/photo1.jpeg" alt="Portrait de SOADAN Koffi Sylvain">
        <figcaption>SOADAN Koffi Sylvain — Lomé, Togo</figcaption>
      </figure>
    </div>
  </section>

  <section class="statement" aria-labelledby="statement-title">
    <div class="container statement-inner" data-reveal>
      <p class="statement-kicker">Positionnement</p>
      <h2 id="statement-title">African Digital Governance &amp; AI Policy</h2>
      <p>Construire progressivement une expertise à l’intersection de la technologie, du droit, des politiques
        publiques et des relations internationales, au service d’institutions africaines et multilatérales.</p>
    </div>
  </section>

  <section class="section" aria-labelledby="about-title">
    <div class="container grid-2" data-reveal>
      <div>
        <p class="eyebrow">Qui je suis</p>
        <h2 id="about-title" class="section-title">Un parcours mené en parallèle de la technique, du droit et du
          politique.</h2>
      </div>
      <div>
        <p>Je suis étudiant et jeune professionnel africain. Je poursuis simultanément une formation en <strong>Génie
            Logiciel &amp; Systèmes d’Information à l’IAI-Togo</strong>, une formation en <strong>droit public à
            l’Université de Lomé</strong> et un cursus en <strong>sciences politiques à l’Université de Kara</strong>.
        </p>
        <p>Cette triple formation nourrit une conviction : les grands enjeux numériques africains ne peuvent être
          traités séparément par des ingénieurs, des juristes ou des politologues. Ils demandent des profils capables
          de comprendre la technique, la norme, les institutions et les réalités de terrain.</p>
        <p class="mt-2"><a class="btn btn-secondary" href="about.php">Read More</a></p>
      </div>
    </div>
  </section>

  <section class="section section-soft" aria-labelledby="expertise-title">
    <div class="container" data-reveal>
      <div class="section-head">
        <p class="eyebrow">Expertise</p>
        <h2 id="expertise-title" class="section-title">Huit domaines, un même fil directeur.</h2>
        <p class="section-lead">Relier la capacité technique, la norme juridique et la décision publique.</p>
      </div>
      <div class="expertise-grid">
        <span class="expertise-item">AI Governance</span>
        <span class="expertise-item">Digital Governance</span>
        <span class="expertise-item">Cybersecurity</span>
        <span class="expertise-item">Data Governance</span>
        <span class="expertise-item">Public Law</span>
        <span class="expertise-item">Public Policy</span>
        <span class="expertise-item">Technology Diplomacy</span>
        <span class="expertise-item">Software Engineering</span>
      </div>
      <p class="mt-4 mb-0"><a class="btn btn-secondary" href="expertise.php">Voir l’expertise</a></p>
    </div>
  </section>

  <section class="section" aria-labelledby="projects-title">
    <div class="container" data-reveal>
      <div class="section-head">
        <p class="eyebrow">Projets sélectionnés</p>
        <h2 id="projects-title" class="section-title">Des initiatives documentées, à des stades honnêtes.</h2>
        <p class="section-lead">Chaque projet indique clairement son stade réel — concept, recherche, prototype ou
          développement — et les éléments vérifiables qui l’accompagnent.</p>
      </div>
      <div class="project-list">
        <article class="project-row">
          <p class="project-domain">Gouvernance de l’IA · Infrastructure de confiance</p>
          <h3>BITC — Blue Intelligence Technologies Corporation</h3>
          <p class="project-desc">Cadre d’audit algorithmique et de documentation des systèmes d’IA déployés dans
            les services publics, aligné sur la stratégie continentale de l’Union africaine.</p>
          <div class="tag-row">
            <span class="status status-prototype">Prototype</span>
            <span class="tag">AI Governance</span><span class="tag">Audit algorithmique</span><span
              class="tag">Conformité UA</span>
          </div>
          <p class="mt-2 mb-0"><a class="link-inline" href="projects/bitc.php">View Project →</a></p>
        </article>
        <article class="project-row">
          <p class="project-domain">Santé · Infrastructure publique numérique</p>
          <h3>African Health OS</h3>
          <p class="project-desc">Architecture d’une infrastructure publique numérique de santé reposant sur des
            standards ouverts (HL7/FHIR) et un registre cryptographique traçant les actes médicaux.</p>
          <div class="tag-row">
            <span class="status status-concept">Concept</span>
            <span class="tag">HL7/FHIR R4</span><span class="tag">DPI santé</span><span class="tag">Souveraineté des
              données</span>
          </div>
          <p class="mt-2 mb-0"><a class="link-inline" href="projects/african-health-os.php">View Project →</a></p>
        </article>
        <article class="project-row">
          <p class="project-domain">Civic tech · Participation citoyenne</p>
          <h3>4 Voix Jeunesse Togo</h3>
          <p class="project-desc">Plateforme civique géoréférencée permettant de documenter les défaillances
            urbaines, avec floutage spatial (Geohash) et validation par les pairs.</p>
          <div class="tag-row">
            <span class="status status-concept">Concept</span>
            <span class="tag">Civic Tech</span><span class="tag">Confidentialité spatiale</span><span
              class="tag">Gouvernance locale</span>
          </div>
          <p class="mt-2 mb-0"><a class="link-inline" href="projects/4-voix-jeunesse.php">View Project →</a></p>
        </article>
      </div>
      <p class="mt-4 mb-0"><a class="btn btn-secondary" href="projects.php">Voir tous les projets</a></p>
    </div>
  </section>

  <section class="section section-soft" aria-labelledby="research-title">
    <div class="container grid-2" data-reveal>
      <div>
        <p class="eyebrow">Recherche &amp; politique publique</p>
        <h2 id="research-title" class="section-title">Axes de recherche en construction.</h2>
        <p class="section-lead">Mes intérêts portent sur la gouvernance de l’IA, la souveraineté numérique, la
          gouvernance des données et la diplomatie technologique en Afrique. Les travaux publiés et ceux en
          développement sont distingués sur la page dédiée.</p>
        <p class="mt-2"><a class="btn btn-primary" href="research.php">Explore Research</a></p>
      </div>
      <div>
        <ul class="prose">
          <li>Gouvernance de l’IA en Afrique</li>
          <li>Souveraineté numérique</li>
          <li>Gouvernance des données</li>
          <li>Politiques de cybersécurité</li>
          <li>Infrastructures publiques numériques</li>
          <li>Diplomatie technologique</li>
          <li>Transformation numérique</li>
          <li>IA et administration publique</li>
          <li>Technologie et développement africain</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="section" aria-labelledby="journey-title">
    <div class="container" data-reveal>
      <div class="section-head">
        <p class="eyebrow">Parcours professionnel</p>
        <h2 id="journey-title" class="section-title">Education → Experience → Leadership → Projects → Research.</h2>
        <p class="section-lead">Un parcours construit par étapes, dont chaque dimension est documentée sur sa propre
          page.</p>
      </div>
      <div class="journey-grid">
        <a class="journey-step" href="education.php">
          <span class="journey-label">Étape</span>
          <strong>Education</strong>
          <span class="journey-hint">Formations en cours</span>
        </a>
        <a class="journey-step" href="experience.php">
          <span class="journey-label">Étape</span>
          <strong>Experience</strong>
          <span class="journey-hint">Postes et mandats</span>
        </a>
        <a class="journey-step" href="leadership.php">
          <span class="journey-label">Étape</span>
          <strong>Leadership</strong>
          <span class="journey-hint">Engagements</span>
        </a>
        <a class="journey-step" href="projects.php">
          <span class="journey-label">Étape</span>
          <strong>Projects</strong>
          <span class="journey-hint">Initiatives documentées</span>
        </a>
        <a class="journey-step" href="research.php">
          <span class="journey-label">Étape</span>
          <strong>Research</strong>
          <span class="journey-hint">Notes et analyses</span>
        </a>
      </div>
    </div>
  </section>

  <section class="section section-soft" aria-labelledby="dossier-title">
    <div class="container-narrow center" data-reveal>
      <p class="eyebrow">Dossier professionnel</p>
      <h2 id="dossier-title" class="section-title">Un dossier complet, du profil exécutif aux documents.</h2>
      <p class="section-lead" style="margin-inline:auto">Le dossier rassemble profil, formation, expérience,
        leadership, expertises, projets, recherche, certifications et documents, avec un export PDF.</p>
      <div class="hero-actions" style="justify-content:center">
        <a class="btn btn-primary" href="dossier.php">Ouvrir le dossier professionnel</a>
        <a class="btn btn-secondary" href="documents/CV_SKS.pdf" download>Télécharger le CV (PDF)</a>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>