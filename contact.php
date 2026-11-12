<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Page Contact (FR)
 * =============================================================================
 *  Rendu visuel strictement identique à contact.html.
 *  Le formulaire est désormais traité côté serveur (actions/contact.php).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'page.php';

$LANG = 'fr';
$PAGE = 'contact';
$BASE = '';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

start_secure_session();
$csrfToken = csrf_token();

// Statut éventuel renvoyé par le traitement serveur (?status=...).
$status = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string) $_GET['status']) : '';
$statusMessage = $status !== '' ? contact_message($status, $LANG) : '';

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<main id="main">
  <section class="page-hero">
    <div class="container">
      <p class="kicker">Contact</p>
      <h1>Construire des solutions à forte utilité publique.</h1>
      <p class="lead">Ouvert aux stages de haut niveau, bourses d’études, collaborations de recherche, projets
        numériques et opportunités internationales liées à la technologie, au droit et à la gouvernance publique.</p>
    </div>
  </section>

  <section class="section">
    <div class="container grid-2">
      <div>
        <p class="eyebrow">Coordonnées</p>
        <h2 class="section-title">Écrire directement.</h2>
        <ul class="doc-list">
          <li><span class="doc-name">Email</span><a class="link-inline"
              href="mailto:sylvainsoadan3@gmail.com">sylvainsoadan3@gmail.com</a></li>
          <li><span class="doc-name">Téléphone</span><a class="link-inline" href="tel:+22898554612">+228 98 55 46
              12</a></li>
          <li><span class="doc-name">LinkedIn</span><a class="link-inline"
              href="https://www.linkedin.com/in/koffi-sylvain-soadan-747572356/" target="_blank" rel="noopener">Profil
              LinkedIn</a></li>
          <li><span class="doc-name">GitHub</span><span class="doc-meta">Coming soon — lien non disponible</span></li>
          <li><span class="doc-name">Localisation</span><span class="doc-meta">Lomé, Togo</span></li>
        </ul>
        <p class="note">Le bouton GitHub est volontairement désactivé tant qu’aucune URL publique valide n’est
          disponible, afin d’éviter tout lien mort.</p>
      </div>
      <div>
        <p class="eyebrow">Formulaire</p>
        <h2 class="section-title">Message institutionnel.</h2>
        <form id="contact-form" action="actions/contact.php" method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="language" value="fr">
          <input type="hidden" name="form_time" value="<?= e((string) time()) ?>">
          <div class="form-field" style="position:absolute;left:-9999px" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>
          <div class="form-field">
            <label for="name">Nom &amp; titre</label>
            <input id="name" name="name" required placeholder="ex. Dr. Aminata Touré">
          </div>
          <div class="form-field">
            <label for="email">Email institutionnel</label>
            <input id="email" type="email" name="email" required placeholder="ex. a.toure@un.org">
          </div>
          <div class="form-field">
            <label for="phone">Téléphone (optionnel)</label>
            <input id="phone" name="phone" type="tel" placeholder="ex. +228 90 00 00 00">
          </div>
          <div class="form-field">
            <label for="subject">Organisation / sujet</label>
            <input id="subject" name="subject" placeholder="ex. Programme UA / Stage recherche / Projet DPI">
          </div>
          <div class="form-field">
            <label for="collab_type">Type de collaboration</label>
            <select id="collab_type" name="collab_type">
              <option value="">— Sélectionner —</option>
              <option value="stage">Stage</option>
              <option value="bourse">Bourse / fellowship</option>
              <option value="recherche">Collaboration de recherche</option>
              <option value="projet">Projet numérique / DPI</option>
              <option value="conference">Conférence / intervention</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required
              placeholder="Votre message ou proposition de collaboration…"></textarea>
          </div>
          <button class="btn btn-primary" type="submit">Transmettre le message</button>
          <div id="form-status" class="form-status" <?= $statusMessage === '' ? ' hidden' : '' ?>><?= e($statusMessage) ?>
          </div>
          <noscript>
            <p class="form-note">Le formulaire fonctionne sans JavaScript : il est traité directement par le serveur.
            </p>
          </noscript>
        </form>
      </div>
    </div>
  </section>
</main>

<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>