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
        <p class="kicker">Project 02 — Health &amp; digital public infrastructure</p>
        <h1>African Health OS</h1>
        <p class="lead">An architecture for a health digital public infrastructure connecting patients, care centres,
          pharmacies and authorities around open standards (HL7/FHIR) and sovereign data.</p>
        <div class="tag-row">
          <span class="status status-concept">Concept</span>
          <span class="tag">HL7/FHIR R4</span><span class="tag">Health DPI</span><span class="tag">Data
            sovereignty</span>
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
          <p>African Health OS is a conceptual health digital public infrastructure (DPI) architecture designed for
            pan-African interoperability: unique patient records, clinical data exchange and traceability of care
            events.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Field</dt>
                <dd>Digital Public Infrastructure (Health)</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Data standard</dt>
                <dd>HL7 FHIR Release 4</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Reference</dt>
                <dd>Africa CDC digital health strategy</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Stage</dt>
                <dd>Concept</dd>
              </dl>
            </div>
          </div>
        </section>

        <section id="problem">
          <h2>02 — Problem</h2>
          <p>In many West African health systems, medical records are still kept on paper or locked into
            non-communicating proprietary software. When a patient is transferred from a rural clinic to a university
            hospital, their history, allergies and prescriptions are frequently lost.</p>
          <p>This information gap leads to diagnostic delays, costly duplicate tests and an inability for ministries to
            steer health policy and epidemic alerts in real time.</p>
        </section>

        <section id="objective">
          <h2>03 — Objective</h2>
          <p>To describe an interoperability layer able to interconnect health actors without forcing the replacement of
            existing tools, while guaranteeing data residency and the protection of clinical data.</p>
        </section>

        <section id="solution">
          <h2>04 — Proposed solution</h2>
          <ul>
            <li><strong>Unique patient identifier:</strong> identity reconciliation enabling a lifelong record
              regardless of facility.</li>
            <li><strong>Offline &amp; USSD access:</strong> asynchronous synchronisation for rural centres and
              code-based access for users without smartphones.</li>
            <li><strong>Pharmaceutical supply chain:</strong> traceability of essential medicines and detection of
              stock-outs.</li>
            <li><strong>Epidemiological surveillance:</strong> anonymised aggregation of diagnoses at national level.
            </li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — My role</h2>
          <p>Architecture design, drafting of the FHIR patient identity profile and development of a cryptographic
            audit-ledger prototype. A personal applied-research effort, with no institutional commission at this stage.
          </p>
        </section>

        <section id="tech">
          <h2>06 — Technology / methodology</h2>
          <p>A data profile compliant with <strong>HL7 FHIR R4</strong>, a <strong>Python</strong> prototype of a hash
            chain (SHA-256) guaranteeing the non-repudiation of care events, and granular-consent principles inspired by
            SMART on FHIR.</p>
        </section>

        <section id="governance">
          <h2>07 — Governance &amp; policy dimension</h2>
          <ul>
            <li><strong>Granular consent:</strong> citizens control who may access their record, with the ability to
              revoke.</li>
            <li><strong>Territorial residency:</strong> hosting in national data centres.</li>
            <li><strong>Immutable audit log:</strong> a timestamped proof for every access.</li>
            <li><strong>Framework:</strong> the Malabo Convention on cybersecurity and personal data protection.</li>
          </ul>
        </section>

        <section id="status">
          <h2>08 — Current status</h2>
          <p><strong>Concept.</strong> The architecture, the patient schema and an audit-ledger prototype exist. No
            deployment, pilot or health partnership is in place.</p>
        </section>

        <section id="evidence">
          <h2>09 — Evidence</h2>
          <ul>
            <li>HL7 FHIR R4 patient identity profile (JSON).</li>
            <li>Cryptographic audit-ledger prototype (Python).</li>
            <li>System specification (technical README).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Roadmap</h2>
          <ol>
            <li>Consolidate the FHIR profiles and adapt the schemas to national identity systems.</li>
            <li>Document consent and emergency cross-border sharing scenarios.</li>
            <li>Study a pilot with an academic or health actor — <em>to be defined</em>.</li>
          </ol>
          <p class="note">No health partnership is engaged. Any real implementation would fall to the competent health
            authorities.</p>
        </section>

        <section id="impact">
          <h2>11 — Potential impact</h2>
          <p>Better continuity of care between facilities, fewer redundant tests and real-time health surveillance
            capacity — subject to validation by health authorities and compliance with data protection frameworks.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">HL7 FHIR R4 patient profile (JSON)</span><a class="link-inline"
                href="../../artefacts/african-health-os/schemas/patient-identifier-fhir.json">View</a></li>
            <li><span class="doc-name">Cryptographic audit ledger (Python)</span><a class="link-inline"
                href="../../artefacts/african-health-os/core/audit_trail_hasher.py">View</a></li>
            <li><span class="doc-name">System specification (README)</span><a class="link-inline"
                href="../../artefacts/african-health-os/README.md">View</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Links</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Public code repository</span><span class="doc-meta">Coming soon — no public
                repository available</span></li>
            <li><span class="doc-name">Related projects</span><a class="link-inline" href="bitc.php">BITC — AI
                Governance</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Propose a partnership</a>
            <a class="btn btn-secondary" href="../projects.php">All projects</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
