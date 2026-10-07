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
        <p class="kicker">Project 01 — AI governance</p>
        <h1>BITC — Blue Intelligence Technologies Corporation</h1>
        <p class="lead">A conceptual trust infrastructure to audit, document and govern artificial intelligence systems
          deployed in African public services.</p>
        <div class="tag-row">
          <span class="status status-prototype">Prototype</span>
          <span class="tag">AI Governance</span><span class="tag">Algorithmic audit</span><span class="tag">African
            Union compliance</span>
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
          <li><a href="#tech">Technology</a></li>
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
          <p>BITC is a framework for a trust infrastructure aimed at African public institutions and regulators. It
            seeks to make the AI systems used in sensitive areas (health, finance, predictive justice, administration)
            inspectable, documentable and monitorable.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Field</dt>
                <dd>AI Governance &amp; Risk</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Alignment</dt>
                <dd>African Union AI Strategy</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Key component</dt>
                <dd>AI digital passport</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stage</dt>
                <dd>Prototype</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problem</h2>
          <p>Most AI models used in Africa are developed outside the continent, trained on exogenous data and rarely
            assessed against criteria adapted to African linguistic, cultural and legal realities.</p>
          <p>Without a technical audit framework, administrations are exposed to algorithmic bias, to unregulated export
            of sensitive data and to a loss of decision-making control over systems that directly affect users.</p>
        </section>

        <section id="objective">
          <h2>03 — Objective</h2>
          <p>To design a technical and methodological foundation to: document each deployed AI system (data origin,
            purpose, limits, accountability); assess statistical disparities and bias risks; monitor post-deployment
            performance and detect drift; and produce verifiable evidence of compliance with African and international
            frameworks.</p>
        </section>

        <section id="solution">
          <h2>04 — Proposed solution</h2>
          <ul>
            <li><strong>Registry &amp; AI passport:</strong> a documented identity record for each model, including
              training-data traceability.</li>
            <li><strong>Risk &amp; bias engine:</strong> quantitative assessment of statistical parity and robustness.
            </li>
            <li><strong>Continuous audit:</strong> post-deployment monitoring (data drift, performance degradation).
            </li>
            <li><strong>Compliance gateway:</strong> risk classification and generation of a trust score.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — My role</h2>
          <p>Conception of the framework, drafting of specifications (AI passport schema, audit engine logic) and
            development of the audit prototype. This project is a personal applied-research effort and is not part of
            any institutional commission at this stage.</p>
        </section>

        <section id="tech">
          <h2>06 — Technology / methodology</h2>
          <p>Schema specification in <strong>JSON Schema</strong>, a <strong>Python</strong> prototype for calculating
            the disparate impact ratio (DIR) and the trust score, aligned with risk classifications (NIST AI RMF, logic
            comparable to the EU AI Act).</p>
        </section>

        <section id="governance">
          <h2>07 — Governance &amp; policy dimension</h2>
          <p>The framework is built around existing references:</p>
          <ul>
            <li><strong>African Union Continental AI Strategy (2024)</strong> — local capacity, African languages,
              national data protection.</li>
            <li><strong>UNESCO Recommendation on the Ethics of AI (2021)</strong> — transparency, explainability,
              fairness.</li>
            <li><strong>Malabo Convention</strong> — sovereignty of cross-border flows and personal data protection.
            </li>
          </ul>
        </section>

        <section id="status">
          <h2>08 — Current status</h2>
          <p><strong>Prototype.</strong> The conceptual framework, the AI passport schema and a bias-audit prototype
            exist. No production deployment has taken place. No institutional partner is engaged to date.</p>
        </section>

        <section id="evidence">
          <h2>09 — Evidence</h2>
          <ul>
            <li>AI digital passport schema specification (JSON Schema).</li>
            <li>Bias-audit and trust-score prototype (Python).</li>
            <li>Associated analysis note (Policy Brief #01).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Roadmap</h2>
          <ol>
            <li>Consolidate the AI passport specification and document the data model.</li>
            <li>Extend the audit prototype and add reproducible tests.</li>
            <li>Study the feasibility of a supervised pilot with an academic or regulatory partner — <em>to be
                defined</em>.</li>
          </ol>
          <p class="note">Any pilot or deployment step depends on an institutional partnership that is not yet
            established.</p>
        </section>

        <section id="impact">
          <h2>11 — Potential impact</h2>
          <p>In time, such a framework could help regulators and administrations assess the AI systems they use,
            document automated decisions and strengthen public trust — subject to validation by competent institutions.
          </p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">AI passport schema (JSON Schema)</span><a class="link-inline"
                href="../../artefacts/bitc-framework/schemas/ai-passport-schema.json">View</a></li>
            <li><span class="doc-name">Bias-audit prototype (Python)</span><a class="link-inline"
                href="../../artefacts/bitc-framework/core/bias_audit_sample.py">View</a></li>
            <li><span class="doc-name">Technical README</span><a class="link-inline"
                href="../../artefacts/bitc-framework/README.md">View</a></li>
            <li><span class="doc-name">Policy Brief #01 — AI Governance in Africa</span><a class="link-inline"
                href="../publications/policy-brief-01.php">Read</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Links</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Public code repository</span><span class="doc-meta">Coming soon — no public
                repository available</span></li>
            <li><span class="doc-name">Related projects</span><a class="link-inline"
                href="digital-sovereignty-observatory.php">African Digital Sovereignty Observatory</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Discuss this project</a>
            <a class="btn btn-secondary" href="../projects.php">All projects</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
