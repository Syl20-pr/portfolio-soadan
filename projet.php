<?php
/**
 * =============================================================================
 *  Portfolio SKS — Fiche projet dynamique (/projet.php?slug=…)
 * =============================================================================
 *  Les 6 fiches statiques existantes restent à projects/*.php.
 *  Ce fichier sert les projets créés depuis l'administration.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = current_lang();
$BASE = '';

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$project = [];

try {
    if ($slug !== '') {
        $stmt = db()->prepare('SELECT * FROM projects WHERE slug = :s AND status = "published" LIMIT 1');
        $stmt->execute([':s' => $slug]);
        $row = $stmt->fetch();
        $project = $row !== false ? $row : [];
    }
} catch (Throwable $e) {
    log_event('error', 'Dynamic project: unavailable');
    $project = [];
}

if ($project === [] || $project === null) {
    header('HTTP/1.1 404 Not Found', true, 404);
    $TITLE = 'Projet introuvable — SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = '';
} else {
    $TITLE = (string) $project['title'] . ' — SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = mb_substr((string) $project['summary'], 0, 320, 'UTF-8');
}

require __DIR__ . '/includes/header.php';
?>

<main id="main">
<?php if ($project === [] || $project === null): ?>
  <section class="section">
    <div class="container-narrow center">
      <h1 class="section-title">Projet introuvable</h1>
      <p class="section-lead">Ce projet n'existe pas ou n'est pas encore publié.</p>
      <p class="mt-2"><a class="btn btn-secondary" href="projects.php">Tous les projets</a></p>
    </div>
  </section>
<?php else: ?>
  <article class="container-narrow" style="padding:4rem 0 6rem">
    <p class="eyebrow"><?= e((string) ($project['role'] ?? '')) ?></p>
    <h1 class="section-title" style="font-size:clamp(1.8rem,4.5vw,2.6rem)"><?= e($project['title']) ?></h1>
    <?php if ((string) $project['summary'] !== ''): ?>
      <p class="section-lead"><?= e($project['summary']) ?></p>
    <?php endif; ?>

    <?php if (!empty($project['image'])): ?>
      <figure style="margin:2rem 0">
        <img src="<?= e('uploads/images/' . $project['image']) ?>" alt="<?= e($project['title']) ?>"
             style="width:100%;height:auto;border-radius:8px;border:1px solid #dfe3e8" loading="lazy">
      </figure>
    <?php endif; ?>

    <?php
    $ds = !empty($project['date_start']) ? date('m/Y', strtotime((string) $project['date_start'])) : '';
    $de = !empty($project['date_end']) ? date('m/Y', strtotime((string) $project['date_end'])) : 'en cours';
    ?>
    <div class="spec-grid" style="margin:2rem 0">
      <?php if ($ds !== ''): ?>
        <div class="spec-item"><dl><dt>Période</dt><dd><?= e($ds . ' → ' . $de) ?></dd></dl></div>
      <?php endif; ?>
      <?php if ((string) $project['partners'] !== ''): ?>
        <div class="spec-item"><dl><dt>Partenaires</dt><dd><?= e($project['partners']) ?></dd></dl></div>
      <?php endif; ?>
      <?php if (!empty($project['external_url'])): ?>
        <div class="spec-item"><dl><dt>Lien</dt>
          <dd><a class="link-inline" href="<?= e($project['external_url']) ?>" target="_blank" rel="noopener noopener noreferrer">Visiter ↗</a></dd>
        </dl></div>
      <?php endif; ?>
    </div>

    <div class="prose" style="margin-top:1rem">
      <?= $project['description_html'] /* authored by the trusted administrator */ ?>
    </div>

    <?php if ((string) $project['objectives_html'] !== ''): ?>
      <h2 class="section-title" style="font-size:1.4rem;margin-top:2.5rem">Objectifs</h2>
      <div class="prose"><?= $project['objectives_html'] ?></div>
    <?php endif; ?>

    <?php if ((string) $project['results_html'] !== ''): ?>
      <h2 class="section-title" style="font-size:1.4rem;margin-top:2.5rem">Résultats</h2>
      <div class="prose"><?= $project['results_html'] ?></div>
    <?php endif; ?>

    <?php
    // Images supplémentaires (galerie façon LinkedIn) — clic pour agrandir.
    try { $extraImgs = media_images_for('project', (int) $project['id']); } catch (Throwable $e) { $extraImgs = []; }
    if (!empty($extraImgs)): ?>
      <section class="extra-gallery" style="margin-top:2.5rem">
        <h2 style="font-size:1.2rem;color:var(--navy)"><?= $LANG === 'en' ? 'In pictures' : 'En images' ?></h2>
        <div class="exgrid">
          <?php foreach ($extraImgs as $im):
            $cap = $LANG === 'en' ? $im['caption_en'] : $im['caption_fr'];
            $alt = $LANG === 'en' ? $im['alt_en'] : $im['alt_fr'];
          ?>
            <figure class="exitem">
              <a href="<?= e('uploads/images/' . $im['filename']) ?>" target="_blank" rel="noopener">
                <img src="<?= e('uploads/images/' . $im['filename']) ?>" alt="<?= e($alt !== '' ? $alt : $cap) ?>" loading="lazy" decoding="async">
              </a>
              <?php if (trim((string) $cap) !== ''): ?><figcaption><?= e($cap) ?></figcaption><?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <p class="mt-4"><a class="btn btn-secondary" href="projects.php">← Tous les projets</a></p>
  </article>
<?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
