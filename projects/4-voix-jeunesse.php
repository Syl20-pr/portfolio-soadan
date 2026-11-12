<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/page.php';

$LANG = 'fr';
$PAGE = 'projects';
$BASE = '../';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/../includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">Projet 03 — Civic tech &amp; participation citoyenne</p>
        <h1>4 Voix Jeunesse Togo</h1>
        <p class="lead">Plateforme civique géoréférencée conçue pour permettre aux citoyens de documenter, vérifier et
          suivre la résolution des défaillances urbaines en lien avec les municipalités.</p>
        <div class="tag-row">
          <span class="status status-concept">Concept</span>
          <span class="tag">Civic Tech</span><span class="tag">Confidentialité spatiale</span><span
            class="tag">Gouvernance locale</span>
        </div>
      </div>
    </section>

    <div class="container-narrow">
      <nav aria-label="Sections du projet" class="mt-4">
        <ul class="case-index">
          <li><a href="#overview">Vue d’ensemble</a></li>
          <li><a href="#problem">Problème</a></li>
          <li><a href="#objective">Objectif</a></li>
          <li><a href="#solution">Solution proposée</a></li>
          <li><a href="#role">Mon rôle</a></li>
          <li><a href="#tech">Technologie</a></li>
          <li><a href="#governance">Dimension gouvernance</a></li>
          <li><a href="#status">Stade actuel</a></li>
          <li><a href="#evidence">Éléments documentés</a></li>
          <li><a href="#roadmap">Feuille de route</a></li>
          <li><a href="#impact">Impact potentiel</a></li>
          <li><a href="#documents">Documents</a></li>
          <li><a href="#links">Liens</a></li>
        </ul>
      </nav>

      <article class="prose" style="padding-bottom:8rem">
        <section id="overview">
          <h2>01 — Vue d’ensemble</h2>
          <p>4 Voix Jeunesse Togo est une plateforme civique destinée à favoriser l’expression et la participation des
            jeunes, en transformant un signalement citoyen (voirie, salubrité, eau) en un suivi transparent jusqu’à sa
            résolution par les services municipaux.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Domaine</dt>
                <dd>Civic Tech &amp; gouvernement ouvert</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Cœur technique</dt>
                <dd>Géolocalisation &amp; horodatage</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Confidentialité</dt>
                <dd>Floutage spatial différentiel</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stade</dt>
                <dd>Concept</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problème</h2>
          <p>Avec la décentralisation territoriale au Togo et en Afrique de l’Ouest, les communes font face à un défi
            d’information : les pannes d’éclairage, les dépôts d’ordures ou les ruptures d’adduction d’eau mettent du
            temps à remonter aux services techniques compétents.</p>
          <p>Les citoyens disposent pourtant d’outils numériques simples. Le manque d’un canal structuré — et la crainte
            de représailles pour les lanceurs d’alerte — limitent leur participation effective.</p>
        </section>

        <section id="objective">
          <h2>03 — Objectif</h2>
          <p>Créer un canal civique fiable : un signalement horodaté et géolocalisé, protégé par un mécanisme de
            confidentialité, publié avec un suivi d’état transparent (signalé → pris en compte → en cours de traitement
            → résolu).</p>
        </section>

        <section id="solution">
          <h2>04 — Solution proposée</h2>
          <ul>
            <li><strong>Signalement horodaté et géolocalisé</strong> avec preuve photo/vidéo.</li>
            <li><strong>Confidentialité spatiale différentielle :</strong> les coordonnées exactes sont floutées via un
              algorithme de tuilage Geohash.</li>
            <li><strong>Consensus par les pairs :</strong> une alerte n’apparaît sur le tableau de bord municipal
              qu’après confirmation par plusieurs utilisateurs proches.</li>
            <li><strong>Suivi d’état transparent</strong> de la réclamation jusqu’à sa résolution.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — Mon rôle</h2>
          <p>Conception du concept, du parcours citoyen et des mécanismes de confidentialité, rédaction du schéma de
            signalement et développement d’un prototype d’anonymisation spatiale. Démarche personnelle, sans commande
            municipale à ce stade.</p>
        </section>

        <section id="tech">
          <h2>06 — Technologie / méthodologie</h2>
          <p>Schéma de données de type <strong>JSON / GeoJSON</strong> pour les signalements et prototype
            <strong>Python</strong> d’anonymisation par Geohash (floutage à rayon configurable, 500 m à 1 000 m).</p>
        </section>

        <section id="governance">
          <h2>07 — Dimension gouvernance &amp; politiques publiques</h2>
          <p>Le projet s’inscrit dans le contexte de la décentralisation et vise un cadre d’interopérabilité entre
            citoyens et communes. Il intègre par conception la protection des données de l’alerteur, en cohérence avec
            la loi togolaise sur la protection des données et les principes de confidentialité.</p>
        </section>

        <section id="status">
          <h2>08 — Stade actuel</h2>
          <p><strong>Concept.</strong> Le scénario d’usage, le schéma de données et un prototype d’anonymisation
            existent. Aucune commune n’est partenaire et aucune plateforme n’est en service.</p>
        </section>

        <section id="evidence">
          <h2>09 — Éléments documentés</h2>
          <ul>
            <li>Schéma de signalement citoyen (JSON Schema / GeoJSON).</li>
            <li>Prototype d’anonymisation spatiale Geohash (Python).</li>
            <li>Guide d’interopérabilité mairie–citoyens (spécification).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Feuille de route</h2>
          <ol>
            <li>Consolider le schéma de signalement et le cycle de vie des alertes.</li>
            <li>Documenter un protocole d’intégration pour les services techniques municipaux.</li>
            <li>Étudier un pilote avec une commune volontaire — <em>à définir</em>.</li>
          </ol>
          <p class="note">Tout pilote dépendrait de l’accord d’une collectivité locale, qui n’est pas encore établi.</p>
        </section>

        <section id="impact">
          <h2>11 — Impact potentiel</h2>
          <p>Une meilleure remontée des besoins locaux, une participation citoyenne plus directe et un suivi public des
            engagements municipaux — sous réserve d’un partenariat effectif avec les collectivités.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Schéma de signalement citoyen (JSON)</span><a class="link-inline"
                href="../artefacts/4voix-civic-tech/schemas/citizen-report-schema.json">Consulter</a></li>
            <li><span class="doc-name">Prototype d’anonymisation Geohash (Python)</span><a class="link-inline"
                href="../artefacts/4voix-civic-tech/core/geohash_anonymizer.py">Consulter</a></li>
            <li><span class="doc-name">Spécification système (README)</span><a class="link-inline"
                href="../artefacts/4voix-civic-tech/README.md">Consulter</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Liens</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Dépôt de code public</span><span class="doc-meta">Coming soon — aucun dépôt
                public disponible</span></li>
            <li><span class="doc-name">Projets liés</span><a class="link-inline" href="tjt.php">Tribune de la Jeunesse
                Togolaise</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Découvrir le concept &amp; collaborer</a>
            <a class="btn btn-secondary" href="../projects.php">Tous les projets</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
