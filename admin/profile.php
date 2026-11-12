<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : profil, parcours et paramètres
 * =============================================================================
 *  Gestion bilingue (FR/EN) des expériences, formations, compétences,
 *  certifications, distinctions, biographie et coordonnées.
 *  Chaque section se gère sous forme de table simple (ajout / édition / suppression).
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$flash = '';
$flashType = 'ok';

try {
    $pdo = db();

    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');
            $clean = static function ($v, int $max): string {
                $v = is_scalar($v) ? (string) $v : '';
                $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
                return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
            };

            if ($action === 'settings') {
                foreach (['contact_email' => 254, 'contact_location' => 160, 'contact_linkedin' => 300,
                          'contact_x' => 300, 'bio_fr' => 20000, 'bio_en' => 20000] as $k => $max) {
                    setting_set($pdo, $k, $clean($_POST[$k] ?? '', $max));
                }
                $flash = 'Paramètres enregistrés.';
            } elseif ($action === 'delete_row') {
                $table = (string) ($_POST['table'] ?? '');
                $allowed = ['experiences', 'education', 'skills', 'certifications', 'distinctions'];
                if (in_array($table, $allowed, true)) {
                    $pdo->prepare('DELETE FROM ' . $table . ' WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
                    $flash = 'Élément supprimé.';
                }
            } elseif ($action === 'add_experience') {
                $pdo->prepare(
                    'INSERT INTO experiences (org_fr, org_en, role_fr, role_en, desc_fr, desc_en, location, date_start, date_end, is_current, sort_order)
                     VALUES (:o1, :o2, :r1, :r2, :d1, :d2, :loc, :ds, :de, :cur, 0)'
                )->execute([
                    ':o1' => $clean($_POST['org_fr'] ?? '', 200), ':o2' => $clean($_POST['org_en'] ?? '', 200),
                    ':r1' => $clean($_POST['role_fr'] ?? '', 200), ':r2' => $clean($_POST['role_en'] ?? '', 200),
                    ':d1' => $clean($_POST['desc_fr'] ?? '', 2000), ':d2' => $clean($_POST['desc_en'] ?? '', 2000),
                    ':loc' => $clean($_POST['location'] ?? '', 160),
                    ':ds' => trim((string) ($_POST['date_start'] ?? '')) ?: null,
                    ':de' => trim((string) ($_POST['date_end'] ?? '')) ?: null,
                    ':cur' => isset($_POST['is_current']) ? 1 : 0,
                ]);
                $flash = 'Expérience ajoutée.';
            } elseif ($action === 'add_education') {
                $pdo->prepare(
                    'INSERT INTO education (school_fr, school_en, degree_fr, degree_en, desc_fr, desc_en, started, ended, is_current, sort_order)
                     VALUES (:s1, :s2, :d1, :d2, :x1, :x2, :st, :en, :cur, 0)'
                )->execute([
                    ':s1' => $clean($_POST['school_fr'] ?? '', 200), ':s2' => $clean($_POST['school_en'] ?? '', 200),
                    ':d1' => $clean($_POST['degree_fr'] ?? '', 200), ':d2' => $clean($_POST['degree_en'] ?? '', 200),
                    ':x1' => $clean($_POST['desc_fr'] ?? '', 2000), ':x2' => $clean($_POST['desc_en'] ?? '', 2000),
                    ':st' => (int) ($_POST['started'] ?? 0) ?: null,
                    ':en' => (int) ($_POST['ended'] ?? 0) ?: null,
                    ':cur' => isset($_POST['is_current']) ? 1 : 0,
                ]);
                $flash = 'Formation ajoutée.';
            } elseif ($action === 'add_skill') {
                $group = in_array(($_POST['group_name'] ?? 'expertise'), ['expertise', 'technical'], true) ? ($_POST['group_name'] ?? 'expertise') : 'expertise';
                $pdo->prepare('INSERT INTO skills (name, name_en, group_name, level, sort_order) VALUES (:n, :ne, :g, :l, 0)')
                    ->execute([
                        ':n' => $clean($_POST['name'] ?? '', 120),
                        ':ne' => $clean($_POST['name_en'] ?? '', 120),
                        ':g' => $group,
                        ':l' => $clean($_POST['level'] ?? '', 40),
                    ]);
                $flash = 'Compétence ajoutée.';
            } elseif ($action === 'add_certification') {
                $pdo->prepare('INSERT INTO certifications (name_fr, name_en, issuer, year, url, sort_order) VALUES (:n1, :n2, :i, :y, :u, 0)')
                    ->execute([
                        ':n1' => $clean($_POST['name_fr'] ?? '', 200), ':n2' => $clean($_POST['name_en'] ?? '', 200),
                        ':i' => $clean($_POST['issuer'] ?? '', 160),
                        ':y' => (int) ($_POST['year'] ?? 0) ?: null,
                        ':u' => $clean($_POST['url'] ?? '', 400),
                    ]);
                $flash = 'Certification ajoutée.';
            } elseif ($action === 'add_distinction') {
                $pdo->prepare('INSERT INTO distinctions (title_fr, title_en, issuer, year, desc_fr, desc_en, sort_order) VALUES (:t1, :t2, :i, :y, :d1, :d2, 0)')
                    ->execute([
                        ':t1' => $clean($_POST['title_fr'] ?? '', 200), ':t2' => $clean($_POST['title_en'] ?? '', 200),
                        ':i' => $clean($_POST['issuer'] ?? '', 160),
                        ':y' => (int) ($_POST['year'] ?? 0) ?: null,
                        ':d1' => $clean($_POST['desc_fr'] ?? '', 400), ':d2' => $clean($_POST['desc_en'] ?? '', 400),
                    ]);
                $flash = 'Distinction ajoutée.';
            }
        }
        header('Location: profile.php', true, 303);
        exit;
    }
} catch (Throwable $e) {
    log_event('error', 'Admin profile: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$exps = $edus = $skls = $certs = $dists = [];
$settings = [];
try {
    $exps = experiences_all();
    $edus = education_all();
    $skls = skills_all();
    $certs = certifications_all();
    $dists = distinctions_all();
    $settings = settings_all();
} catch (Throwable $e) {
    log_event('error', 'Admin profile: data unavailable');
}

admin_header('Profil & Paramètres', 'profile');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Coordonnées et présentation</h2>
  <form method="POST" action="profile.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="settings">
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="contact_email">Email professionnel</label>
        <input id="contact_email" name="contact_email" class="adm-input" maxlength="254" value="<?= e($settings['contact_email'] ?? '') ?>">
      </div>
      <div class="adm-field">
        <label for="contact_location">Localisation</label>
        <input id="contact_location" name="contact_location" class="adm-input" maxlength="160" value="<?= e($settings['contact_location'] ?? '') ?>">
      </div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="contact_linkedin">Profil LinkedIn (URL)</label>
        <input id="contact_linkedin" name="contact_linkedin" class="adm-input" maxlength="300" value="<?= e($settings['contact_linkedin'] ?? '') ?>">
      </div>
      <div class="adm-field">
        <label for="contact_x">Compte X / Twitter (URL)</label>
        <input id="contact_x" name="contact_x" class="adm-input" maxlength="300" value="<?= e($settings['contact_x'] ?? '') ?>">
      </div>
    </div>
    <div class="adm-field">
      <label for="bio_fr">Biographie (français) — utilisée pour les données structurées SEO</label>
      <textarea id="bio_fr" name="bio_fr" class="adm-textarea" rows="4"><?= e($settings['bio_fr'] ?? '') ?></textarea>
    </div>
    <div class="adm-field">
      <label for="bio_en">Biography (English)</label>
      <textarea id="bio_en" name="bio_en" class="adm-textarea" rows="4"><?= e($settings['bio_en'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="adm-btn">Enregistrer</button>
  </form>
</div>

<?php
/** Petit tableau de listing avec suppression. */
function profile_section_table(array $rows, string $table, array $columns, callable $render): void
{
    if (empty($rows)) {
        echo '<p class="adm-muted">Aucun élément enregistré.</p>';
        return;
    }
    echo '<table class="adm-table"><thead><tr>';
    foreach ($columns as $col) echo '<th>' . e($col) . '</th>';
    echo '<th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($render($r) as $cell) echo '<td>' . e($cell) . '</td>';
        echo '<td><form method="POST" onsubmit="return confirm(\'Supprimer cet élément ?\');">';
        csrf_field();
        echo '<input type="hidden" name="action" value="delete_row">';
        echo '<input type="hidden" name="table" value="' . e($table) . '">';
        echo '<input type="hidden" name="id" value="' . (int) $r['id'] . '">';
        echo '<button type="submit" class="adm-btn small danger">Supprimer</button></form></td></tr>';
    }
    echo '</tbody></table>';
}
?>

<div class="adm-grid2">
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Expériences</h2>
    <?php
    profile_section_table($exps, 'experiences', ['Organisation', 'Rôle', 'Période'], static function ($r) {
        $p = ($r['date_start'] ?? '—') . ' → ' . ($r['is_current'] ? 'en cours' : ($r['date_end'] ?? '—'));
        return [$r['org_fr'], $r['role_fr'], $p];
    });
    ?>
    <details style="margin-top:12px">
      <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une expérience</summary>
      <form method="POST" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_experience">
        <div class="adm-grid2">
          <div class="adm-field"><label>Organisation (FR)</label><input class="adm-input" name="org_fr" required></div>
          <div class="adm-field"><label>Organisation (EN)</label><input class="adm-input" name="org_en"></div>
        </div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Rôle (FR)</label><input class="adm-input" name="role_fr" required></div>
          <div class="adm-field"><label>Rôle (EN)</label><input class="adm-input" name="role_en"></div>
        </div>
        <div class="adm-field"><label>Description (FR)</label><textarea class="adm-textarea" rows="3" name="desc_fr"></textarea></div>
        <div class="adm-field"><label>Description (EN)</label><textarea class="adm-textarea" rows="3" name="desc_en"></textarea></div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Lieu</label><input class="adm-input" name="location"></div>
          <div class="adm-field"><label>Actuellement en poste</label><input type="checkbox" name="is_current"></div>
        </div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Début</label><input type="date" class="adm-input" name="date_start"></div>
          <div class="adm-field"><label>Fin</label><input type="date" class="adm-input" name="date_end"></div>
        </div>
        <button type="submit" class="adm-btn">Ajouter</button>
      </form>
    </details>
  </div>

  <div class="adm-card">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Formations</h2>
    <?php
    profile_section_table($edus, 'education', ['Établissement', 'Diplôme', 'Années'], static function ($r) {
        return [$r['school_fr'], $r['degree_fr'], ($r['started'] ?? '—') . '–' . ($r['is_current'] ? 'en cours' : ($r['ended'] ?? '—'))];
    });
    ?>
    <details style="margin-top:12px">
      <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une formation</summary>
      <form method="POST" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_education">
        <div class="adm-grid2">
          <div class="adm-field"><label>Établissement (FR)</label><input class="adm-input" name="school_fr" required></div>
          <div class="adm-field"><label>Établissement (EN)</label><input class="adm-input" name="school_en"></div>
        </div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Diplôme (FR)</label><input class="adm-input" name="degree_fr" required></div>
          <div class="adm-field"><label>Degree (EN)</label><input class="adm-input" name="degree_en"></div>
        </div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Année de début</label><input type="number" class="adm-input" name="started" min="1990" max="2100"></div>
          <div class="adm-field"><label>Année de fin (vide = en cours)</label><input type="number" class="adm-input" name="ended" min="1990" max="2100"></div>
        </div>
        <div class="adm-field"><label>En cours</label><input type="checkbox" name="is_current"></div>
        <button type="submit" class="adm-btn">Ajouter</button>
      </form>
    </details>
  </div>
</div>

<div class="adm-grid2">
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Compétences</h2>
    <?php
    profile_section_table($skls, 'skills', ['Compétence', 'Groupe'], static function ($r) {
        return [$r['name'], $r['group_name']];
    });
    ?>
    <details style="margin-top:12px">
      <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une compétence</summary>
      <form method="POST" style="margin-top:10px" class="adm-flex" style="align-items:flex-end">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_skill">
        <div class="adm-field"><label>Nom (FR)</label><input class="adm-input" name="name" required></div>
        <div class="adm-field"><label>Name (EN)</label><input class="adm-input" name="name_en"></div>
        <div class="adm-field">
          <label>Groupe</label>
          <select class="adm-select" name="group_name" style="width:auto">
            <option value="expertise">Expertise</option>
            <option value="technical">Technique</option>
          </select>
        </div>
        <button type="submit" class="adm-btn">Ajouter</button>
      </form>
    </details>
  </div>

  <div class="adm-card">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Certifications</h2>
    <?php
    profile_section_table($certs, 'certifications', ['Nom', 'Émetteur', 'Année'], static function ($r) {
        return [$r['name_fr'], $r['issuer'], (string) ($r['year'] ?? '—')];
    });
    ?>
    <details style="margin-top:12px">
      <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une certification</summary>
      <form method="POST" style="margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_certification">
        <div class="adm-grid2">
          <div class="adm-field"><label>Nom (FR)</label><input class="adm-input" name="name_fr" required></div>
          <div class="adm-field"><label>Name (EN)</label><input class="adm-input" name="name_en"></div>
        </div>
        <div class="adm-grid2">
          <div class="adm-field"><label>Émetteur</label><input class="adm-input" name="issuer"></div>
          <div class="adm-field"><label>Année</label><input type="number" class="adm-input" name="year" min="2000" max="2100"></div>
        </div>
        <div class="adm-field"><label>URL de vérification</label><input class="adm-input" name="url"></div>
        <button type="submit" class="adm-btn">Ajouter</button>
      </form>
    </details>
  </div>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:17px;color:#0a243b">Distinctions</h2>
  <?php
  profile_section_table($dists, 'distinctions', ['Titre', 'Émetteur', 'Année'], static function ($r) {
      return [$r['title_fr'], $r['issuer'], (string) ($r['year'] ?? '—')];
  });
  ?>
  <details style="margin-top:12px">
    <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une distinction</summary>
    <form method="POST" style="margin-top:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_distinction">
      <div class="adm-grid2">
        <div class="adm-field"><label>Titre (FR)</label><input class="adm-input" name="title_fr" required></div>
        <div class="adm-field"><label>Title (EN)</label><input class="adm-input" name="title_en"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Émetteur</label><input class="adm-input" name="issuer"></div>
        <div class="adm-field"><label>Année</label><input type="number" class="adm-input" name="year" min="2000" max="2100"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Détail (FR)</label><input class="adm-input" name="desc_fr"></div>
        <div class="adm-field"><label>Détail (EN)</label><input class="adm-input" name="desc_en"></div>
      </div>
      <button type="submit" class="adm-btn">Ajouter</button>
    </form>
  </details>
</div>

<?php admin_footer(); ?>
