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
        <p class="kicker">Projet 01 — Gouvernance de l’IA</p>
        <h1>BITC — Blue Intelligence Technologies Corporation</h1>
        <p class="lead">Cadre conceptuel d’infrastructure de confiance pour auditer, documenter et gouverner les
          systèmes d’intelligence artificielle déployés dans les services publics africains.</p>
        <div class="tag-row">
          <span class="status status-prototype">Prototype</span>
          <span class="tag">AI Governance</span><span class="tag">Audit algorithmique</span><span class="tag">Conformité
            Union africaine</span>
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
          <p>BITC est un cadre pour une infrastructure de confiance destinée aux institutions publiques et régulateurs
            africains. Il vise à rendre inspectables, documentables et surveillables les systèmes d’IA utilisés dans des
            domaines sensibles (santé, finance, justice prédictive, administration).</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Domaine</dt>
                <dd>AI Governance &amp; Risk</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Alignement</dt>
                <dd>Stratégie IA de l’Union africaine</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Composante clé</dt>
                <dd>Passeport numérique de l’IA</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stade</dt>
                <dd>Prototype</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problème</h2>
          <p>La majorité des modèles d’IA utilisés en Afrique sont développés hors du continent, entraînés sur des
            données exogènes et rarement évalués selon des critères adaptés aux réalités linguistiques, culturelles et
            juridiques africaines.</p>
          <p>Faute de cadre d’audit technique, les administrations s’exposent à des biais algorithmiques, à
            l’exportation non encadrée de données sensibles et à une perte de maîtrise décisionnelle sur des systèmes
            qui affectent directement les usagers.</p>
        </section>

        <section id="objective">
          <h2>03 — Objectif</h2>
          <p>Concevoir un socle technique et méthodologique permettant de :</p>
          <ul>
            <li>documenter chaque système d’IA déployé (origine des données, finalité, limites, responsabilité) ;</li>
            <li>évaluer les disparités statistiques et les risques de biais ;</li>
            <li>suivre les performances après déploiement et détecter les dérives ;</li>
            <li>produire des preuves vérifiables de conformité aux référentiels africains et internationaux.</li>
          </ul>
        </section>

        <section id="solution">
          <h2>04 — Solution proposée</h2>
          <p>Une architecture en quatre modules :</p>
          <ul>
            <li><strong>Registre &amp; passeport IA :</strong> fiche d’identité documentée pour chaque modèle, incluant
              la traçabilité des données d’entraînement.</li>
            <li><strong>Moteur de risque et de biais :</strong> évaluation quantitative de la parité statistique et de
              la robustesse.</li>
            <li><strong>Audit continu :</strong> surveillance post-déploiement (dérive de données, dégradation des
              performances).</li>
            <li><strong>Passerelle de conformité :</strong> classification par niveau de risque et génération d’un
              indice de confiance.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — Mon rôle</h2>
          <p>Conception du cadre, rédaction des spécifications (schéma de passeport IA, logique du moteur d’audit) et
            développement du prototype d’audit. Ce projet relève d’une démarche personnelle de recherche appliquée et ne
            s’inscrit dans aucune commande institutionnelle à ce stade.</p>
        </section>

        <section id="tech">
          <h2>06 — Technologie / méthodologie</h2>
          <p>Spécification de schéma au format <strong>JSON Schema</strong>, prototype en <strong>Python</strong> pour
            le calcul de l’indice d’impact disparate (DIR) et de l’indice de confiance, alignement sur les
            classifications de risque (NIST AI RMF, logique comparable à l’EU AI Act).</p>
        </section>

        <section id="governance">
          <h2>07 — Dimension gouvernance &amp; politiques publiques</h2>
          <p>Le cadre s’articule autour de référentiels existants :</p>
          <ul>
            <li><strong>Stratégie continentale sur l’IA de l’Union africaine (2024)</strong> — capacités locales,
              langues africaines, protection des données nationales.</li>
            <li><strong>Recommandation de l’UNESCO sur l’éthique de l’IA (2021)</strong> — transparence, explicabilité,
              équité.</li>
            <li><strong>Convention de Malabo</strong> — souveraineté des flux transfrontaliers et protection des données
              personnelles.</li>
          </ul>
        </section>

        <section id="status">
          <h2>08 — Stade actuel</h2>
          <p><strong>Prototype.</strong> Le cadre conceptuel, le schéma de passeport IA et un prototype d’audit de biais
            existent. Aucun déploiement en production n’a eu lieu. Aucun partenaire institutionnel n’est engagé à ce
            jour.</p>
        </section>

        <section id="evidence">
          <h2>09 — Éléments documentés</h2>
          <ul>
            <li>Spécification de schéma de passeport numérique de l’IA (JSON Schema).</li>
            <li>Prototype d’audit de biais et de calcul d’indice de confiance (Python).</li>
            <li>Note d’analyse associée (Policy Brief #01).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Feuille de route</h2>
          <ol>
            <li>Consolider la spécification du passeport IA et documenter le modèle de données.</li>
            <li>Étendre le prototype d’audit et ajouter des tests reproductibles.</li>
            <li>Étudier la faisabilité d’un pilote encadré avec un partenaire académique ou régulateur — <em>à
                définir</em>.</li>
          </ol>
          <p class="note">Toute étape de pilote ou de déploiement dépend d’un partenariat institutionnel qui n’est pas
            encore établi.</p>
        </section>

        <section id="impact">
          <h2>11 — Impact potentiel</h2>
          <p>À terme, un tel cadre pourrait aider les régulateurs et administrations à évaluer les systèmes d’IA qu’ils
            utilisent, à documenter les décisions automatisées et à renforcer la confiance publique — sous réserve de
            validation par des institutions compétentes.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Schéma de passeport IA (JSON Schema)</span><a class="link-inline"
                href="../artefacts/bitc-framework/schemas/ai-passport-schema.json">Consulter</a></li>
            <li><span class="doc-name">Prototype d’audit de biais (Python)</span><a class="link-inline"
                href="../artefacts/bitc-framework/core/bias_audit_sample.py">Consulter</a></li>
            <li><span class="doc-name">Note de cadrage (README technique)</span><a class="link-inline"
                href="../artefacts/bitc-framework/README.md">Consulter</a></li>
            <li><span class="doc-name">Policy Brief #01 — Gouvernance de l’IA en Afrique</span><a class="link-inline"
                href="../publications/policy-brief-01.php">Lire</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Liens</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Dépôt de code public</span><span class="doc-meta">Coming soon — aucun dépôt
                public disponible</span></li>
            <li><span class="doc-name">Projets liés</span><a class="link-inline"
                href="digital-sovereignty-observatory.php">African Digital Sovereignty Observatory</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Échanger sur ce projet</a>
            <a class="btn btn-secondary" href="../projects.php">Tous les projets</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
