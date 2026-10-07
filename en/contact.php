<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Contact page (EN)
 * =============================================================================
 *  Rendu visuel strictement identique à en/contact.html.
 *  Le formulaire est traité côté serveur par ../actions/contact.php.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'page.php';

$LANG = 'en';
$PAGE = 'contact';
$BASE = '../';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

start_secure_session();
$csrfToken = csrf_token();

$status = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string) $_GET['status']) : '';
$statusMessage = $status !== '' ? contact_message($status, $LANG) : '';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<main id="main">
  <section class="page-hero">
    <div class="container">
      <p class="kicker">Contact</p>
      <h1>Building solutions of lasting public value.</h1>
      <p class="lead">Open to high-level internships, fellowships, research collaborations, digital projects and
        international opportunities related to technology, law and public governance.</p>
    </div>
  </section>

  <section class="section">
    <div class="container grid-2">
      <div>
        <p class="eyebrow">Details</p>
        <h2 class="section-title">Write directly.</h2>
        <ul class="doc-list">
          <li><span class="doc-name">Email</span><a class="link-inline"
              href="mailto:sylvainsoadan3@gmail.com">sylvainsoadan3@gmail.com</a></li>
          <li><span class="doc-name">Phone</span><a class="link-inline" href="tel:+22898554612">+228 98 55 46 12</a>
          </li>
          <li><span class="doc-name">LinkedIn</span><a class="link-inline"
              href="https://www.linkedin.com/in/koffi-sylvain-soadan-747572356/" target="_blank" rel="noopener">LinkedIn
              profile</a></li>
          <li><span class="doc-name">GitHub</span><span class="doc-meta">Coming soon — link not available</span></li>
          <li><span class="doc-name">Location</span><span class="doc-meta">Lomé, Togo</span></li>
        </ul>
        <p class="note">The GitHub button is intentionally disabled until a valid public URL is available, to avoid
          any dead link.</p>
      </div>
      <div>
        <p class="eyebrow">Form</p>
        <h2 class="section-title">Institutional message.</h2>
        <form id="contact-form" action="../actions/contact.php" method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="language" value="en">
          <input type="hidden" name="form_time" value="<?= e((string) time()) ?>">
          <div class="form-field" style="position:absolute;left:-9999px" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>
          <div class="form-field">
            <label for="name">Name &amp; title</label>
            <input id="name" name="name" required placeholder="e.g. Dr. Aminata Touré">
          </div>
          <div class="form-field">
            <label for="email">Institutional email</label>
            <input id="email" type="email" name="email" required placeholder="e.g. a.toure@un.org">
          </div>
          <div class="form-field">
            <label for="phone">Phone (optional)</label>
            <input id="phone" name="phone" type="tel" placeholder="e.g. +228 90 00 00 00">
          </div>
          <div class="form-field">
            <label for="subject">Organisation / subject</label>
            <input id="subject" name="subject" placeholder="e.g. AU programme / Research fellowship / DPI project">
          </div>
          <div class="form-field">
            <label for="collab_type">Type of collaboration</label>
            <select id="collab_type" name="collab_type">
              <option value="">— Select —</option>
              <option value="internship">Internship</option>
              <option value="fellowship">Fellowship</option>
              <option value="research">Research collaboration</option>
              <option value="project">Digital / DPI project</option>
              <option value="conference">Conference / speaking</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required
              placeholder="Describe your inquiry or proposed collaboration…"></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Send message</button>
          <div id="form-status" class="form-status" <?= $statusMessage === '' ? ' hidden' : '' ?>><?= e($statusMessage) ?>
          </div>
          <noscript>
            <p class="form-note">The form works without JavaScript: it is handled directly by the server.</p>
          </noscript>
        </form>
      </div>
    </div>
  </section>
</main>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>