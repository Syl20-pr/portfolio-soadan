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
        <p class="kicker">Project 03 — Civic tech &amp; citizen participation</p>
        <h1>4 Voix Jeunesse Togo</h1>
        <p class="lead">A georeferenced civic platform designed to let citizens document, verify and track the
          resolution of urban failures in coordination with municipalities.</p>
        <div class="tag-row">
          <span class="status status-concept">Concept</span>
          <span class="tag">Civic Tech</span><span class="tag">Spatial privacy</span><span class="tag">Local
            governance</span>
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
          <p>4 Voix Jeunesse Togo is a civic platform intended to foster youth expression and participation, by turning
            a citizen report (roads, sanitation, water) into transparent follow-up until the municipal services resolve
            it.</p>
          <div class="spec-grid">
            <div class="spec-item">
              <dl>
                <dt>Field</dt>
                <dd>Civic Tech &amp; open government</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Core technique</dt>
                <dd>Geolocation &amp; timestamping</dd>
              </dl>
            </div>
            <div class="spec-item">
              <dl>
                <dt>Privacy</dt>
                <dd>Spatial differential blurring</dd>
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
          <p>With territorial decentralisation in Togo and West Africa, municipalities face an information challenge:
            lighting outages, illegal waste dumps or water supply failures take time to reach the competent technical
            services.</p>
          <p>Citizens already have simple digital tools. The lack of a structured channel — and the fear of retaliation
            faced by whistle-blowers — limits their effective participation.</p>
        </section>

        <section id="objective">
          <h2>03 — Objective</h2>
          <p>To create a reliable civic channel: a timestamped and geolocated report, protected by a privacy mechanism,
            published with transparent status tracking (reported → acknowledged → being processed → resolved).</p>
        </section>

        <section id="solution">
          <h2>04 — Proposed solution</h2>
          <ul>
            <li><strong>Timestamped and geolocated report</strong> with photo/video evidence.</li>
            <li><strong>Spatial differential privacy:</strong> exact coordinates are blurred using a Geohash tiling
              algorithm.</li>
            <li><strong>Peer consensus:</strong> a report only appears on the municipal dashboard after confirmation by
              several nearby users.</li>
            <li><strong>Transparent status tracking</strong> of the complaint until resolution.</li>
          </ul>
        </section>

        <section id="role">
          <h2>05 — My role</h2>
          <p>Conception of the concept, the citizen journey and the privacy mechanisms, drafting of the report schema
            and development of a spatial anonymisation prototype. A personal effort, with no municipal commission at
            this stage.</p>
        </section>

        <section id="tech">
          <h2>06 — Technology / methodology</h2>
          <p>A <strong>JSON / GeoJSON</strong> data schema for reports and a <strong>Python</strong> prototype of
            Geohash anonymisation (configurable blur radius, 500 m to 1,000 m).</p>
        </section>

        <section id="governance">
          <h2>07 — Governance &amp; policy dimension</h2>
          <p>The project sits within the decentralisation context and aims at an interoperability framework between
            citizens and municipalities. It incorporates reporter data protection by design, consistent with Togolese
            data protection law and privacy principles.</p>
        </section>

        <section id="status">
          <h2>08 — Current status</h2>
          <p><strong>Concept.</strong> The use case, the data schema and an anonymisation prototype exist. No
            municipality is a partner and no platform is in service.</p>
        </section>

        <section id="evidence">
          <h2>09 — Evidence</h2>
          <ul>
            <li>Citizen report schema (JSON Schema / GeoJSON).</li>
            <li>Geohash spatial anonymisation prototype (Python).</li>
            <li>Municipality–citizen interoperability guide (specification).</li>
          </ul>
        </section>

        <section id="roadmap">
          <h2>10 — Roadmap</h2>
          <ol>
            <li>Consolidate the report schema and the report lifecycle.</li>
            <li>Document an integration protocol for municipal technical services.</li>
            <li>Study a pilot with a willing municipality — <em>to be defined</em>.</li>
          </ol>
          <p class="note">Any pilot would depend on the agreement of a local authority, which is not yet established.
          </p>
        </section>

        <section id="impact">
          <h2>11 — Potential impact</h2>
          <p>Better reporting of local needs, more direct citizen participation and public tracking of municipal
            commitments — subject to an effective partnership with local authorities.</p>
        </section>

        <section id="documents">
          <h2>12 — Documents</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Citizen report schema (JSON)</span><a class="link-inline"
                href="../../artefacts/4voix-civic-tech/schemas/citizen-report-schema.json">View</a></li>
            <li><span class="doc-name">Geohash anonymisation prototype (Python)</span><a class="link-inline"
                href="../../artefacts/4voix-civic-tech/core/geohash_anonymizer.py">View</a></li>
            <li><span class="doc-name">System specification (README)</span><a class="link-inline"
                href="../../artefacts/4voix-civic-tech/README.md">View</a></li>
          </ul>
        </section>

        <section id="links">
          <h2>13 — Links</h2>
          <ul class="doc-list">
            <li><span class="doc-name">Public code repository</span><span class="doc-meta">Coming soon — no public
                repository available</span></li>
            <li><span class="doc-name">Related projects</span><a class="link-inline" href="tjt.php">Togolese Youth
                Tribune</a></li>
          </ul>
          <p class="mt-4 mb-0">
            <a class="btn btn-primary" href="../contact.php">Explore the concept &amp; collaborate</a>
            <a class="btn btn-secondary" href="../projects.php">All projects</a>
          </p>
        </section>
      </article>
    </div>
  </main>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
