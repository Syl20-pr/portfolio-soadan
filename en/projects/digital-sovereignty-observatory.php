<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/page.php';

$LANG = 'en';
$PAGE = 'projects';
$BASE = '../../';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/../../includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">Project 06 — Research &amp; comparative analysis</p>
        <h1>African Digital Sovereignty Observatory</h1>
        <p class="lead">A research and analysis project mapping national legislation, sovereign cloud infrastructure and
          cross-border data flows in Africa.</p>
        <div class="tag-row">
          <span class="status status-research">Research</span>
          <span class="tag">Comparative public law</span><span class="tag">Malabo Convention</span><span
            class="tag">Composite index</span>
        </div>
      </div>
    </section>

    <div class="container-narrow">
      <nav aria-label="Project sections" class="mt-4">
        <ul class="case-index">
          <li><a href="#overview">Overview</a></li>
          <li><a href="#problem">Problem</a></li>
          <li><a href="#objective">Objective</a></li>
          <li><a href="#solution">Proposed solution</a></li>
          <li><a href="#role">My role</a></li>
          <li><a href="#tech">Methodology</a></li>
          <li><a href="#governance">Governance dimension</a></li>
          <li><a href="#status">Current status</a></li>
          <li><a href="#evidence">Evidence</a></li>
          <li><a href="#roadmap">Roadmap</a></li>
          <li><a href="#impact">Potential impact</a></li>
          <li><a href="#documents">Documents</a></li>
          <li><a href="#links">Links</a></li>
        </ul>
      </nav>

      <article class="prose" style="padding-bottom:8rem">
        <section id="overview">
          <h2>01 — Overview</h2>
          <p>The Observatory is a research project intended to objectify the state of African digital sovereignty: where
            critical data is hosted, which countries have operational data protection authorities, and which agreements
            govern transfers.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Field</dt>
                <dd>Comparative public law &amp; tech policy</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Methodology</dt>
                <dd>Legal analysis &amp; infrastructure metrics</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Reference</dt>
                <dd>Malabo Convention &amp; AfCFTA</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stage</dt>
                <dd>Research</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problem</h2>
          <p>The debate on African digital sovereignty often rests on impressions rather than comparable data. There is
            a lack of a homogeneous empirical base covering the legal, infrastructure and governance dimensions
            together.</p>
        </section>

        <section id="objective">
          <h2>03 — Objective</h2>
          <p>To provide researchers, decision-makers and multilateral organisations with comparative data and analysis
            to guide public policy design and treaty negotiation.</p>
        </section>

        <section id="solution">
          <h2>04 — Proposed solution</h2>
          <ul>
            <li><strong>Legal pillar:</strong> data and cybersecurity laws, ratification of the Malabo Convention.</li>
            <li><strong>Infrastructure pillar:</strong> local data centre capacity, Internet exchange points.</li>
            <li><strong>Governance pillar:</strong> national data protection authorities, AI strategies.</li>
            <li><strong>A composite index</strong> with a comparative dashboard.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — My role</h2>
          <p>Conception of the methodology, definition of the pillars and development of a prototype scoring engine. A
            personal research project, with no institutional commission.</p>
        </section>

        <section id="tech">
          <h2>06 — Methodology / tools</h2>
          <p>A structured data taxonomy (<strong>JSON</strong>) and a prototype <strong>weighted scoring engine in
              Python</strong> aggregating the legal, infrastructure and governance pillars.</p>
        </section>

        <section id="governance">
          <h2>07 — Governance &amp; policy dimension</h2>
          <p>The project sits within debates on the Malabo Convention, the continental AI strategy and the AfCFTA
            digital protocol. It aims to equip comparative analysis of national frameworks.</p>
        </section>

        <section id="status">
          <h2>08 — Current status</h2>
          <p><strong>Research.</strong> The taxonomy, a sample dataset and a prototype scoring engine exist. State
            coverage is not complete and no publication has yet appeared.</p>
        </section>

        <section id="evidence">
          <h2>09 — Evidence</h2>
          <ul>
            <li>Sample dataset (JSON) across several states.</li>
            <li>Composite scoring engine prototype (Python).</li>
            <li>Methodology note (README).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Roadmap</h2>
          <ol>
            <li>Extend and verify the database across African Union member states.</li>
            <li>Publish the scoring methodology openly.</li>
            <li>Prepare a first research note — <em>Research in Development</em>.</li>
          </ol>
          <p class="note">Any publication will depend on validation of the collected data.</p>
        </section>

        <section id="impact">
          <h2>11 — Potential impact</h2>
          <p>Providing a shared empirical base for African debates on digital sovereignty and supporting evidence-based
            policy design — subject to the robustness of the data.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Sample dataset (JSON)</span><a class="link-inline"
                href="../../artefacts/sovereignty-observatory/data/sovereignty_index_sample.json">View</a></li>
            <li><span class="doc-name">Composite scoring engine (Python)</span><a class="link-inline"
                href="../../artefacts/sovereignty-observatory/core/scoring_engine.py">View</a></li>
            <li><span class="doc-name">Methodology note (README)</span><a class="link-inline"
                href="../../artefacts/sovereignty-observatory/README.md">View</a></li>
            <li><span class="doc-name">Policy Brief #01 — AI Governance in Africa</span><a class="link-inline"
                href="../publications/policy-brief-01.php">Read</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Links</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Public dataset</span><span class="doc-meta">Coming soon — publication to
                come</span></li>
            <li><span class="doc-name">Related projects</span><a class="link-inline" href="bitc.php">BITC — AI
                Governance</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Collaborate with the Observatory</a>
            <a class="btn btn-secondary" href="../projects.php">All projects</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
