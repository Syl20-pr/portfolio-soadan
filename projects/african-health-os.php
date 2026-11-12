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
        <p class="kicker">Projet 02 — Santé &amp; infrastructure publique numérique</p>
        <h1>African Health OS</h1>
        <p class="lead">Architecture d’une infrastructure publique numérique de santé reliant patients, centres de
          soins, pharmacies et autorités autour de standards ouverts (HL7/FHIR) et de données souveraines.</p>
        <div class="tag-row">
          <span class="status status-concept">Concept</span>
          <span class="tag">HL7/FHIR R4</span><span class="tag">DPI santé</span><span class="tag">Souveraineté des
            données</span>
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
          <p>African Health OS est une architecture conceptuelle d’infrastructure publique numérique (DPI) de santé,
            pensée pour l’interopérabilité panafricaine : dossiers patients uniques, échange de données cliniques et
            traçabilité des actes.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Domaine</dt>
                <dd>Digital Public Infrastructure (Santé)</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Standard de données</dt>
                <dd>HL7 FHIR Release 4</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Référentiel</dt>
                <dd>Stratégie santé numérique Africa CDC</dd>
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
          <p>Dans de nombreux systèmes de santé ouest-africains, les dossiers médicaux restent tenus sur support papier
            ou cloisonnés dans des logiciels propriétaires non communicants. Lorsqu’un patient est transféré d’un
            dispensaire rural vers un centre hospitalier, son historique, ses allergies et ses prescriptions sont
            souvent perdus.</p>
          <p>Cette rupture d’information entraîne des retards diagnostiques, des doublons d’examens coûteux et une
            incapacité pour les ministères à piloter en temps réel les politiques sanitaires et les alertes épidémiques.
          </p>
        </section>

        <section id="objective">
          <h2>03 — Objectif</h2>
          <p>Décrire une couche d’interopérabilité capable d’interconnecter les acteurs de santé sans imposer le
            remplacement des outils existants, en garantissant la résidence des données et la protection des données
            cliniques.</p>
        </section>

        <section id="solution">
          <h2>04 — Solution proposée</h2>
          <ul>
            <li><strong>Identifiant patient unique :</strong> réconciliation d’identité permettant un dossier de vie
              quel que soit l’établissement.</li>
            <li><strong>Accès hors ligne &amp; USSD :</strong> synchronisation asynchrone pour les centres ruraux et
              accès par code pour les usagers sans smartphone.</li>
            <li><strong>Chaîne d’approvisionnement pharmaceutique :</strong> traçabilité des médicaments essentiels et
              détection des ruptures.</li>
            <li><strong>Veille épidémiologique :</strong> agrégation anonymisée des diagnostics au niveau national.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — Mon rôle</h2>
          <p>Conception de l’architecture, rédaction du profil d’identité patient FHIR et développement d’un prototype
            de registre d’audit cryptographique. Démarche personnelle de recherche appliquée, sans commande
            institutionnelle à ce stade.</p>
        </section>

        <section id="tech">
          <h2>06 — Technologie / méthodologie</h2>
          <p>Profil de données conforme <strong>HL7 FHIR R4</strong>, prototype en <strong>Python</strong> de chaîne de
            hachage (SHA-256) garantissant la non-répudiation des actes médicaux, principes de consentement granulaire
            inspirés de SMART on FHIR.</p>
        </section>

        <section id="governance">
          <h2>07 — Dimension gouvernance &amp; politiques publiques</h2>
          <ul>
            <li><strong>Consentement granulaire :</strong> le citoyen contrôle qui peut consulter son dossier, avec
              révocation possible.</li>
            <li><strong>Résidence territoriale :</strong> hébergement dans des centres de données nationaux.</li>
            <li><strong>Journal d’audit immuable :</strong> preuve horodatée de chaque consultation.</li>
            <li><strong>Cadre :</strong> Convention de Malabo sur la cybersécurité et la protection des données
              personnelles.</li>
          </ul>
        </section>

        <section id="status">
          <h2>08 — Stade actuel</h2>
          <p><strong>Concept.</strong> L’architecture, le schéma patient et un prototype de registre d’audit existent.
            Aucun déploiement, aucun pilote et aucun partenariat sanitaire ne sont en place.</p>
        </section>

        <section id="evidence">
          <h2>09 — Éléments documentés</h2>
          <ul>
            <li>Profil d’identité patient au format HL7 FHIR R4 (JSON).</li>
            <li>Prototype de registre d’audit cryptographique (Python).</li>
            <li>Spécification système (README technique).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Feuille de route</h2>
          <ol>
            <li>Consolider les profils FHIR et adapter les schémas aux systèmes d’identité nationaux.</li>
            <li>Documenter les scénarios de consentement et de partage transfrontalier d’urgence.</li>
            <li>Étudier un pilote avec un acteur académique ou sanitaire — <em>à définir</em>.</li>
          </ol>
          <p class="note">Aucun partenariat sanitaire n’est engagé. Toute mise en œuvre réelle relèverait des autorités
            sanitaires compétentes.</p>
        </section>

        <section id="impact">
          <h2>11 — Impact potentiel</h2>
          <p>Une meilleure continuité des soins entre établissements, une réduction des examens redondants et une
            capacité de veille sanitaire en temps réel — sous réserve de validation par les autorités sanitaires et de
            conformité aux cadres de protection des données.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Profil patient HL7 FHIR R4 (JSON)</span><a class="link-inline"
                href="../artefacts/african-health-os/schemas/patient-identifier-fhir.json">Consulter</a></li>
            <li><span class="doc-name">Registre d’audit cryptographique (Python)</span><a class="link-inline"
                href="../artefacts/african-health-os/core/audit_trail_hasher.py">Consulter</a></li>
            <li><span class="doc-name">Spécification système (README)</span><a class="link-inline"
                href="../artefacts/african-health-os/README.md">Consulter</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Liens</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Dépôt de code public</span><span class="doc-meta">Coming soon — aucun dépôt
                public disponible</span></li>
            <li><span class="doc-name">Projets liés</span><a class="link-inline" href="bitc.php">BITC — Gouvernance de
                l’IA</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Proposer un partenariat</a>
            <a class="btn btn-secondary" href="../projects.php">Tous les projets</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
