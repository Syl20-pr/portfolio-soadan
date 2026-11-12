<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/page.php';

$LANG = 'en';
$PAGE = 'research';
$BASE = '../';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/../includes/header.php';
?>


  <main id="main">
    <section class="page-hero">
      <div class="container">
        <p class="kicker">Research &amp; publications</p>
        <h1>Building an African analytical voice.</h1>
        <p class="lead">This page presents my research interests, available publications and work in development. Work
          not yet published is clearly flagged as such.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Research interests</p>
          <h2 class="section-title">Fields of interest.</h2>
        </div>
        <div class="grid-3">
          <article class="card green">
            <h3>AI governance in Africa</h3>
            <p>Audit frameworks, algorithmic passports and alignment with the African Union continental strategy.</p>
          </article>
          <article class="card green">
            <h3>Digital sovereignty</h3>
            <p>National legislation, critical data hosting and infrastructure autonomy.</p>
          </article>
          <article class="card green">
            <h3>Data governance</h3>
            <p>Personal data protection, cross-border flows and the Malabo Convention.</p>
          </article>
          <article class="card gold">
            <h3>Cybersecurity policy</h3>
            <p>Critical infrastructure protection, attribution and regional cooperation.</p>
          </article>
          <article class="card gold">
            <h3>Digital public infrastructure</h3>
            <p>Interoperability, identity, health and digital public services.</p>
          </article>
          <article class="card gold">
            <h3>Technology diplomacy</h3>
            <p>Treaty negotiations, global standards and African representation.</p>
          </article>
          <article class="card">
            <h3>Digital transformation</h3>
            <p>Public administration, local capacity and technology adoption.</p>
          </article>
          <article class="card">
            <h3>AI &amp; public administration</h3>
            <p>Automated decision-making, transparency and institutional accountability.</p>
          </article>
          <article class="card">
            <h3>Technology &amp; African development</h3>
            <p>Local innovation, digital public goods and development.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-soft">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Publications</p>
          <h2 class="section-title">Available analysis notes.</h2>
        </div>
        <div class="grid-2">
          <article class="card gold">
            <p class="project-domain">Policy Brief #01</p>
            <h3>AI Governance in Africa: From Technological Dependency to Strategic Sovereignty</h3>
            <p>Analysis of the continental AI strategy and proposals for an independent algorithmic audit framework.</p>
            <p class="mb-0"><a class="link-inline" href="publications/policy-brief-01.php">Read the note →</a></p>
          </article>
          <article class="card gold">
            <p class="project-domain">Policy Brief #02</p>
            <h3>Cybersecurity as Foreign Policy in Africa</h3>
            <p>From the Malabo Convention to collective regional resilience: cybersecurity, attribution and diplomacy.
            </p>
            <p class="mb-0"><a class="link-inline" href="publications/policy-brief-02.php">Read the note →</a></p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Work in progress</p>
          <h2 class="section-title">Research in development.</h2>
        </div>
        <div class="table-wrap">
          <table class="data">
            <caption>Announced work, not yet published.</caption>
            <thead>
              <tr>
                <th scope="col">Title</th>
                <th scope="col">Status</th>
                <th scope="col">Theme</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Digital public infrastructure &amp; fundamental rights</td>
                <td><strong>Research in Development</strong></td>
                <td>Civil registries, FHIR health interoperability and constitutional protection of biometric data.</td>
              </tr>
              <tr>
                <td>Critical data and sovereign hosting in the AfCFTA area</td>
                <td><strong>Research in Development</strong></td>
                <td>Cross-border flows, sovereign cloud and technological autonomy.</td>
              </tr>
              <tr>
                <td>African digital diplomacy and technology treaty negotiation</td>
                <td><strong>Upcoming Publication</strong></td>
                <td>African representation in multilateral forums and global standards.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="mt-4 mb-0"><a class="btn btn-secondary" href="contact.php">Discuss a research collaboration</a></p>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
