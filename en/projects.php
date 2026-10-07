<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/page.php';

$LANG = 'en';
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
        <p class="kicker">Projects</p>
        <h1>Build, document, govern.</h1>
        <p class="lead">Six initiatives linking technology, public impact and governance questions. Each project states
          its real stage and the documented material available.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="project-list">
          <article class="project-row">
            <div>
              <p class="project-domain">AI governance · Trust infrastructure</p>
              <h3>BITC — Blue Intelligence Technologies Corporation</h3>
              <p class="project-desc">Conceptual and technical framework to audit, document and govern AI systems
                deployed in public administration: an “AI Passport”, statistical bias assessment (DIR) and alignment
                with the African Union strategy.</p>
              <div class="tag-row">
                <span class="status status-prototype">Prototype</span>
                <span class="tag">AI Governance</span><span class="tag">Algorithmic audit</span><span class="tag">AU
                  compliance</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/bitc.php">Full case study →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Health · Digital public infrastructure</p>
              <h3>African Health OS</h3>
              <p class="project-desc">Architecture for a health digital public infrastructure connecting patients,
                hospitals, pharmacies and authorities around open standards (HL7/FHIR) and a tamper-evident
                cryptographic ledger.</p>
              <div class="tag-row">
                <span class="status status-concept">Concept</span>
                <span class="tag">HL7/FHIR R4</span><span class="tag">Health DPI</span><span class="tag">Data
                  sovereignty</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/african-health-os.php">Full case study →</a>
              </p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Civic tech · Citizen participation</p>
              <h3>4 Voix Jeunesse Togo</h3>
              <p class="project-desc">A georeferenced civic platform enabling citizens to document urban failures, with
                spatial differential privacy (Geohash) and decentralised peer validation.</p>
              <div class="tag-row">
                <span class="status status-concept">Concept</span>
                <span class="tag">Civic Tech</span><span class="tag">Spatial privacy</span><span class="tag">Local
                  governance</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/4-voix-jeunesse.php">Full case study →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Media · Public debate</p>
              <h3>Togolese Youth Tribune — TJT</h3>
              <p class="project-desc">An independent broadcast and podcast format on society, justice, governance and
                youth, grounded in an editorial neutrality charter and a contradictory verification method.</p>
              <div class="tag-row">
                <span class="status status-development">Development</span>
                <span class="tag">Public debate</span><span class="tag">Fact-checking</span><span class="tag">Rule of
                  law</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/tjt.php">Full case study →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Digital diplomacy · Multilateralism</p>
              <h3>African Youth &amp; Diplomacy — JDA</h3>
              <p class="project-desc">An initiative on youth, diplomacy and African affairs: preparing young
                professionals for technical diplomacy, treaty negotiation and multilateral deliberation.</p>
              <div class="tag-row">
                <span class="status status-research">Research</span>
                <span class="tag">Tech Diplomacy</span><span class="tag">Model AU</span><span
                  class="tag">Treaties</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/jda.php">Full case study →</a></p>
            </div>
          </article>
          <article class="project-row">
            <div>
              <p class="project-domain">Research · Comparative analysis</p>
              <h3>African Digital Sovereignty Observatory</h3>
              <p class="project-desc">A research and analysis project on African digital sovereignty: mapping national
                legislation, sovereign cloud infrastructure and cross-border data flows.</p>
              <div class="tag-row">
                <span class="status status-research">Research</span>
                <span class="tag">55 AU States</span><span class="tag">Malabo Convention</span><span
                  class="tag">Composite index</span>
              </div>
              <p class="mt-2 mb-0"><a class="link-inline" href="projects/digital-sovereignty-observatory.php">Full case
                  study →</a></p>
            </div>
          </article>
        </div>
        <p class="mt-4 note">None of these projects is presented as deployed. The stated stages — concept, research,
          prototype, development — reflect the real, documented progress to date.</p>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
