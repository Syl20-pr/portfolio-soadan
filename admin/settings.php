<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : paramètres généraux du site
 * =============================================================================
 *  N'utilise PAS les mêmes clés que profile.php (coordonnées) : ce fichier
 *  gère les paramètres techniques et éditoriaux globaux.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'un_members.php';
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
            $clean = static function ($v, int $max): string {
                $v = is_scalar($v) ? (string) $v : '';
                $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
                return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
            };
            foreach ([
                'site_statement_fr' => 400,
                'site_statement_en' => 400,
                'seo_default_title' => 160,
                'seo_default_description' => 320,
                'social_linkedin' => 300,
                'social_x' => 300,
                'site_announcement_fr' => 200,
                'site_announcement_en' => 200,
            ] as $k => $max) {
                setting_set($pdo, $k, $clean($_POST[$k] ?? '', $max));
            }
            // Vitesse du bandeau : vient du second formulaire si présent.
            if (isset($_POST['banner_speed_seconds'])) {
                $spd = (int) $_POST['banner_speed_seconds'];
                setting_set($pdo, 'banner_speed_seconds', (string) ($spd >= 10 && $spd <= 300 ? $spd : 40));
            }

            // Diaporama : activation et drapeaux CHOISIS uniquement (défaut : aucun).
            setting_set($pdo, 'banner_enabled', isset($_POST['banner_enabled']) ? '1' : '0');
            setting_set($pdo, 'banner_show_flags', '0'); // obsolète : plus de ligne de drapeaux
            setting_set($pdo, 'banner_two_rows', '0');   // obsolète : diaporama à une scène
            setting_set($pdo, 'banner_flags_mode', 'selected');
            $codes = [];
            foreach ((array) ($_POST['banner_flags_sel'] ?? []) as $c) {
                $c = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $c) ?? '');
                if (strlen($c) === 2 && isset(un_member_states()[$c])) {
                    $codes[] = $c;
                }
            }
            setting_set($pdo, 'banner_flags_codes', implode(',', $codes));

            $flash = 'Paramètres enregistrés.';
        }
        header('Location: settings.php', true, 303);
        exit;
    }
} catch (Throwable $e) {
    log_event('error', 'Admin settings: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$s = [];
try {
    $s = settings_all();
} catch (Throwable $e) {
    log_event('error', 'Admin settings: data unavailable');
}

admin_header('Paramètres', 'settings');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Paramètres du site</h2>
  <p class="adm-help">Ces valeurs enrichissent les métadonnées du site et les données structurées (SEO). Elles ne remplacent jamais les contenus des pages existantes.</p>
  <form method="POST" action="settings.php">
    <?= csrf_field() ?>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="site_statement_fr">Phrase de positionnement (français)</label>
        <textarea id="site_statement_fr" name="site_statement_fr" class="adm-textarea" rows="2" maxlength="400"><?= e($s['site_statement_fr'] ?? '') ?></textarea>
      </div>
      <div class="adm-field">
        <label for="site_statement_en">Positioning statement (English)</label>
        <textarea id="site_statement_en" name="site_statement_en" class="adm-textarea" rows="2" maxlength="400"><?= e($s['site_statement_en'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="seo_default_title">SEO — titre par défaut</label>
        <input id="seo_default_title" name="seo_default_title" class="adm-input" maxlength="160" value="<?= e($s['seo_default_title'] ?? '') ?>">
      </div>
      <div class="adm-field">
        <label for="seo_default_description">SEO — description par défaut</label>
        <input id="seo_default_description" name="seo_default_description" class="adm-input" maxlength="320" value="<?= e($s['seo_default_description'] ?? '') ?>">
      </div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="social_linkedin">LinkedIn (URL complète)</label>
        <input id="social_linkedin" name="social_linkedin" class="adm-input" maxlength="300" value="<?= e($s['social_linkedin'] ?? '') ?>">
      </div>
      <div class="adm-field">
        <label for="social_x">X / Twitter (URL complète)</label>
        <input id="social_x" name="social_x" class="adm-input" maxlength="300" value="<?= e($s['social_x'] ?? '') ?>">
      </div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="site_announcement_fr">Annonce (bandeau discret, français)</label>
        <input id="site_announcement_fr" name="site_announcement_fr" class="adm-input" maxlength="200" value="<?= e($s['site_announcement_fr'] ?? '') ?>">
      </div>
      <div class="adm-field">
        <label for="site_announcement_en">Announcement (English)</label>
        <input id="site_announcement_en" name="site_announcement_en" class="adm-input" maxlength="200" value="<?= e($s['site_announcement_en'] ?? '') ?>">
      </div>
    </div>
    <button type="submit" class="adm-btn">Enregistrer</button>
  </form>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Diaporama de l’accueil</h2>
  <p class="adm-help">Le diaporama affiche les photos de la galerie marquées « Afficher sur l’accueil » (page Galerie), en fondu enchaîné. Les drapeaux, si vous en choisissez, sont intercalés parmi les photos — par défaut : aucun drapeau. Fichiers SVG locaux, aucune ressource externe.</p>
  <form method="POST" action="settings.php">
    <?= csrf_field() ?>
    <p class="adm-flex" style="gap:20px">
      <label style="font-weight:normal"><input type="checkbox" name="banner_enabled" <?= ($s['banner_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Diaporama activé</label>
    </p>
    <div class="adm-field">
      <label for="banner_speed_seconds">Durée totale d’un tour du diaporama (secondes — plus grand = rotation plus lente)</label>
      <input id="banner_speed_seconds" type="number" name="banner_speed_seconds" class="adm-input" min="10" max="300"
             value="<?= e($s['banner_speed_seconds'] ?? '40') ?>" style="width:auto">
    </div>
    <div class="adm-field">
      <label>Drapeaux éventuels (aucun par défaut)</label>
      <input id="flagSearch" type="search" class="adm-input" placeholder="Rechercher un État : Togo, Brésil, Japan…" autocomplete="off" style="max-width:320px">
      <p class="adm-help">Cochez seulement les drapeaux que vous voulez voir alterner parmi vos photos. Sans aucune coche, aucun drapeau n’est affiché.</p>
      <div id="flagList" style="max-height:280px;overflow:auto;border:1px solid var(--line);border-radius:4px;padding:10px;display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:4px">
        <?php
        $members = un_member_states();
        $selected = array_filter(array_map('trim', explode(',', (string) ($s['banner_flags_codes'] ?? ''))));
        foreach ($members as $code => $names):
            [$nameFr, $nameEn] = $names;
        ?>
          <label style="font-weight:normal;font-size:14px" data-search="<?= e(mb_strtolower($nameFr . ' ' . $nameEn . ' ' . $code, 'UTF-8')) ?>">
            <input type="checkbox" name="banner_flags_sel[]" value="<?= e($code) ?>" <?= in_array($code, $selected, true) ? 'checked' : '' ?>>
            <?= e($nameFr) ?> <span class="adm-muted">(<?= e($nameEn) ?>)</span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <button type="submit" class="adm-btn">Enregistrer le diaporama</button>
  </form>
</div>
<script>
(function () {
  var q = document.getElementById('flagSearch');
  if (!q) return;
  q.addEventListener('input', function () {
    var needle = q.value.toLowerCase();
    document.querySelectorAll('#flagList label[data-search]').forEach(function (lab) {
      lab.style.display = (!needle || lab.getAttribute('data-search').indexOf(needle) !== -1) ? '' : 'none';
    });
  });
})();
</script>

<?php admin_footer(); ?>
