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
        <p class="kicker">Projet 06 — Recherche &amp; analyse comparative</p>
        <h1>African Digital Sovereignty Observatory</h1>
        <p class="lead">Projet de recherche et d’analyse cartographiant les législations nationales, les infrastructures
          cloud souveraines et les flux de données transfrontaliers en Afrique.</p>
        <div class="tag-row">
          <span class="status status-research">Recherche</span>
          <span class="tag">Droit public comparé</span><span class="tag">Convention de Malabo</span><span
            class="tag">Indice composite</span>
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
          <li><a href="#tech">Méthodologie</a></li>
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
          <p>L’Observatoire est un projet de recherche destiné à objectiver l’état de la souveraineté numérique
            africaine : où sont hébergées les données critiques, quels pays disposent d’autorités de protection des
            données opérationnelles, quels accords encadrent les transferts.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Domaine</dt>
                <dd>Droit public comparé &amp; politiques technologiques</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Méthodologie</dt>
                <dd>Analyse juridique &amp; métriques d’infrastructure</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Référentiel</dt>
                <dd>Convention de Malabo &amp; ZLECAf</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stade</dt>
                <dd>Recherche</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problème</h2>
          <p>Le débat sur la souveraineté numérique africaine repose souvent sur des impressions plutôt que sur des
            données comparables. Il manque une base empirique homogène couvrant à la fois la dimension juridique,
            l’infrastructure et la gouvernance.</p>
        </section>

        <section id="objective">
          <h2>03 — Objectif</h2>
          <p>Fournir aux chercheurs, décideurs et organisations multilatérales des données et analyses comparatives pour
            guider l’élaboration des politiques publiques et la négociation des traités.</p>
        </section>

        <section id="solution">
          <h2>04 — Solution proposée</h2>
          <ul>
            <li><strong>Pilier juridique :</strong> lois sur les données et la cybersécurité, ratification de la
              Convention de Malabo.</li>
            <li><strong>Pilier infrastructure :</strong> capacité des centres de données locaux, points d’échange
              Internet.</li>
            <li><strong>Pilier gouvernance :</strong> autorités nationales de protection des données, stratégies d’IA.
            </li>
            <li><strong>Indice composite</strong> agrégé, avec tableau de bord comparatif.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — Mon rôle</h2>
          <p>Conception de la méthodologie, définition des piliers et développement d’un prototype de moteur de scoring.
            Projet de recherche personnel, sans commande institutionnelle.</p>
        </section>

        <section id="tech">
          <h2>06 — Méthodologie / outils</h2>
          <p>Taxonomie de données structurée (<strong>JSON</strong>) et prototype de <strong>moteur de scoring pondéré
              en Python</strong> agrégeant les piliers juridique, infrastructurel et de gouvernance.</p>
        </section>

        <section id="governance">
          <h2>07 — Dimension gouvernance &amp; politiques publiques</h2>
          <p>Le projet s’inscrit dans les débats sur la Convention de Malabo, la stratégie continentale de l’IA et le
            protocole numérique de la ZLECAf. Il vise à outiller l’analyse comparative des cadres nationaux.</p>
        </section>

        <section id="status">
          <h2>08 — Stade actuel</h2>
          <p><strong>Recherche.</strong> La taxonomie, un jeu de données d’exemple et un prototype de moteur de scoring
            existent. La couverture des États n’est pas complète et aucune publication n’est encore parue.</p>
        </section>

        <section id="evidence">
          <h2>09 — Éléments documentés</h2>
          <ul>
            <li>Jeu de données d’exemple (JSON) sur plusieurs États.</li>
            <li>Prototype de moteur de scoring composite (Python).</li>
            <li>Note de méthodologie (README).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Feuille de route</h2>
          <ol>
            <li>Étendre et vérifier la base de données sur les États membres de l’Union africaine.</li>
            <li>Documenter publiquement la méthodologie de scoring.</li>
            <li>Préparer une première note de recherche — <em>Research in Development</em>.</li>
          </ol>
          <p class="note">Toute publication dépendra de la validation des données collectées.</p>
        </section>

        <section id="impact">
          <h2>11 — Impact potentiel</h2>
          <p>Offrir une base empirique commune pour les débats africains sur la souveraineté numérique, et soutenir la
            formulation de politiques fondées sur des faits — sous réserve de la robustesse des données.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Jeu de données d’exemple (JSON)</span><a class="link-inline"
                href="../artefacts/sovereignty-observatory/data/sovereignty_index_sample.json">Consulter</a></li>
            <li><span class="doc-name">Moteur de scoring composite (Python)</span><a class="link-inline"
                href="../artefacts/sovereignty-observatory/core/scoring_engine.py">Consulter</a></li>
            <li><span class="doc-name">Note de méthodologie (README)</span><a class="link-inline"
                href="../artefacts/sovereignty-observatory/README.md">Consulter</a></li>
            <li><span class="doc-name">Policy Brief #01 — Gouvernance de l’IA en Afrique</span><a class="link-inline"
                href="../publications/policy-brief-01.php">Lire</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Liens</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Jeu de données public</span><span class="doc-meta">Coming soon — publication à
                venir</span></li>
            <li><span class="doc-name">Projets liés</span><a class="link-inline" href="bitc.php">BITC — Gouvernance de
                l’IA</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Collaborer avec l’Observatoire</a>
            <a class="btn btn-secondary" href="../projects.php">Tous les projets</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
