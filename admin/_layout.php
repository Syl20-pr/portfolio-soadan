<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Gabarit de l'espace administrateur
 * =============================================================================
 *  Espace privé totalement séparé du portfolio public (noindex, robots.txt).
 *  Fournit admin_header() et admin_footer() pour homogénéiser les pages.
 * =============================================================================
 */

declare(strict_types=1);

function admin_header(string $title, string $active = ''): void
{
  $nav = [
    'index' => 'Tableau de bord',
    'messages' => 'Messages',
  ];
  header('X-Robots-Tag: noindex, nofollow', true);
  ?>
  <!doctype html>
  <html lang="fr">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — Administration | SKS</title>
    <style>
      :root {
        --navy: #0a243b;
        --green: #0f6b3c;
        --ink: #17202b;
        --muted: #5a6675;
        --line: #dfe3e8;
        --ivory: #fbfaf7;
      }

      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        font-family: Times New Roman, Georgia, serif;
        color: var(--ink);
        background: var(--ivory);
      }

      a {
        color: var(--green);
      }

      .adm-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px;
      }

      .adm-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid var(--navy);
        padding-bottom: 12px;
        margin-bottom: 24px;
      }

      .adm-bar h1 {
        font-size: 20px;
        margin: 0;
        color: var(--navy);
      }

      .adm-nav {
        display: flex;
        gap: 16px;
      }

      .adm-nav a {
        text-decoration: none;
      }

      .adm-nav a.active {
        font-weight: bold;
        text-decoration: underline;
      }

      .adm-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 6px;
        padding: 18px;
        margin-bottom: 18px;
      }

      .adm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
      }

      .adm-table th,
      .adm-table td {
        text-align: left;
        padding: 9px 10px;
        border-bottom: 1px solid var(--line);
        vertical-align: top;
      }

      .adm-table th {
        color: var(--navy);
      }

      .badge {
        display: inline-block;
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 10px;
      }

      .badge-new {
        background: #fdecea;
        color: #b3261e;
      }

      .badge-read {
        background: #eef4fb;
        color: #17456b;
      }

      .badge-processed {
        background: #e9f5ee;
        color: #0f6b3c;
      }

      .adm-btn {
        display: inline-block;
        padding: 7px 13px;
        background: var(--green);
        color: #fff;
        text-decoration: none;
        border: 0;
        border-radius: 4px;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
      }

      .adm-btn.secondary {
        background: var(--navy);
      }

      .adm-btn.danger {
        background: #b3261e;
      }

      .adm-btn.small {
        padding: 4px 9px;
        font-size: 13px;
      }

      .adm-input,
      .adm-select,
      .adm-textarea {
        width: 100%;
        padding: 9px;
        border: 1px solid var(--line);
        border-radius: 4px;
        font-family: inherit;
        font-size: 15px;
      }

      .adm-field {
        margin-bottom: 16px;
      }

      .adm-field label {
        display: block;
        font-weight: bold;
        color: var(--navy);
        margin-bottom: 6px;
      }

      .adm-alert {
        padding: 11px 14px;
        border-radius: 4px;
        margin-bottom: 16px;
      }

      .adm-alert.ok {
        background: #e9f5ee;
        color: #0f6b3c;
      }

      .adm-alert.err {
        background: #fdecea;
        color: #b3261e;
      }

      .adm-stats {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
      }

      .adm-stat {
        flex: 1 1 160px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 6px;
        padding: 16px;
      }

      .adm-stat strong {
        display: block;
        font-size: 26px;
        color: var(--navy);
      }

      .adm-muted {
        color: var(--muted);
        font-size: 14px;
      }

      .adm-login {
        max-width: 420px;
        margin: 8vh auto;
      }
    </style>
  </head>

  <body>
    <div class="adm-wrap">
      <div class="adm-bar">
        <h1>Administration — Portefolio SKS</h1>
        <nav class="adm-nav">
          <?php foreach ($nav as $key => $label): ?>
            <a href="<?= e($key) ?>.php" class="<?= $key === $active ? 'active' : '' ?>"><?= e($label) ?></a>
          <?php endforeach; ?>
          <a href="logout.php" class="adm-btn small">Déconnexion</a>
        </nav>
      </div>
      <?php
}

function admin_footer(): void
{
  ?>
    </div>
  </body>

  </html>
  <?php
}
