<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Connexion administrateur
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';

$error = '';

if (admin_logged_in()) {
  header('Location: index.php', true, 303);
  exit;
}

if (is_post()) {
  if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    $error = 'Session expirée. Merci de réessayer.';
  } else {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $result = admin_login($email, $password);

    if ($result['ok']) {
      header('Location: index.php', true, 303);
      exit;
    }

    $error = $result['error'] === 'rate'
      ? 'Trop de tentatives échouées. Merci de réessayer dans quelques minutes.'
      : ($result['error'] === 'error'
        ? 'Service temporairement indisponible.'
        : 'Identifiants invalides.');
  }
}
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Connexion — Administration | SKS</title>
  <style>
    body {
      margin: 0;
      font-family: Times New Roman, Georgia, serif;
      background: #fbfaf7;
      color: #17202b;
    }

    .adm-login {
      max-width: 420px;
      margin: 8vh auto;
      background: #fff;
      border: 1px solid #dfe3e8;
      border-radius: 6px;
      padding: 26px;
    }

    h1 {
      font-size: 20px;
      color: #0a243b;
      margin-top: 0;
    }

    label {
      display: block;
      font-weight: bold;
      color: #0a243b;
      margin-bottom: 6px;
    }

    input {
      width: 100%;
      padding: 9px;
      border: 1px solid #dfe3e8;
      border-radius: 4px;
      font-family: inherit;
      font-size: 15px;
    }

    .f {
      margin-bottom: 16px;
    }

    button {
      padding: 10px 16px;
      background: #0f6b3c;
      color: #fff;
      border: 0;
      border-radius: 4px;
      cursor: pointer;
      font-family: inherit;
      font-size: 15px;
    }

    .err {
      background: #fdecea;
      color: #b3261e;
      padding: 11px 14px;
      border-radius: 4px;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>
  <div class="adm-login">
    <h1>Administration — Portfolio SKS</h1>
    <?php if ($error !== ''): ?>
      <div class="err"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <?= csrf_field() ?>
      <div class="f">
        <label for="email">Adresse email</label>
        <input id="email" name="email" type="email" required autocomplete="username">
      </div>
      <div class="f">
        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</body>

</html>