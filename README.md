# Portfolio SOADAN Koffi Sylvain

Portfolio professionnel bilingue (français / anglais) consacré à la gouvernance numérique, aux politiques publiques et aux projets de SOADAN Koffi Sylvain.

## Contenu

- Présentation, parcours, expertise, recherche, publications et projets.
- Pages en français à la racine et pages en anglais dans `en/`.
- Formulaire de contact avec enregistrement des messages en base de données et notification par e-mail.
- Espace d’administration pour consulter les messages reçus.
- Documents et ressources associés aux projets.

## Technologies

- PHP 7.4 ou supérieur
- MySQL 5.7+ ou MariaDB 10.2+
- Apache avec `mod_rewrite` et prise en charge des fichiers `.htaccess`
- Composer pour installer PHPMailer

Extensions PHP nécessaires : PDO MySQL (`pdo_mysql`) et JSON. Un serveur SMTP est recommandé pour l’envoi des e-mails ; l’application peut également utiliser la fonction `mail()` de PHP si PHPMailer ou le SMTP n’est pas configuré.

> GitHub Pages ne peut pas exécuter ce projet PHP. Il faut un hébergement compatible PHP et MySQL/MariaDB.

## Installation locale

1. Placez le projet dans le répertoire servi par Apache et configurez ce répertoire comme racine web du site.
2. À la racine du projet, installez la dépendance PHP :

   ```sh
   composer install --no-dev --optimize-autoloader
   ```

3. Créez la base de données et son utilisateur MySQL/MariaDB, puis importez `database/schema.sql`. Par exemple, depuis un terminal :

   ```sh
   mysql -u portfolio_user -p portfolio_sks < database/schema.sql
   ```

   La base et l’utilisateur doivent être créés au préalable. Le schéma crée les tables des messages, de limitation anti-spam et des tentatives de connexion administrateur.

4. Copiez `.env.example` vers `.env` à la racine du projet et renseignez les valeurs de votre environnement. Sous PowerShell :

   ```powershell
   Copy-Item .env.example .env
   ```

5. Configurez au minimum les paramètres de base de données, `ADMIN_EMAIL`, `ADMIN_PASSWORD_HASH` et `APP_KEY`. Utilisez un mot de passe fort et un hash généré localement avec PHP (`password_hash`). Générez une valeur aléatoire d’au moins 32 caractères pour `APP_KEY`. Ne publiez jamais le fichier `.env`.
6. Pour les notifications e-mail, renseignez les paramètres `SMTP_*`, `MAIL_FROM` et `MAIL_TO`. `MAIL_FROM` doit généralement être une adresse autorisée par votre hébergeur.
7. Vérifiez que le serveur Apache peut lire les fichiers du projet et écrire dans le répertoire `logs/`.

`APP_DEBUG` doit rester à `false` en production. Renseignez `APP_URL` avec l’URL publique du site, et activez HTTPS sur l’hébergement.

## Accès

- Page d’accueil : `/`
- Version anglaise : `/en/`
- Connexion à l’administration : `/admin/login.php`

Les identifiants de l’administration sont définis dans le fichier `.env`. N’utilisez pas `ADMIN_PASSWORD` en clair en production : configurez plutôt `ADMIN_PASSWORD_HASH`.

## Déploiement et sécurité

- Le dépôt GitHub est public : son contenu peut être consulté et cloné par tous. Seuls les collaborateurs autorisés peuvent le modifier selon les permissions configurées dans GitHub.
- Les fichiers `.env` sont ignorés par Git et l’accès à ces fichiers est bloqué par le `.htaccess` fourni. Ne commitez jamais de secrets, de mots de passe ou d’identifiants.
- Les CV présents dans `documents/` sont publiés avec le dépôt et peuvent être téléchargés. Retirez-les du projet si vous ne souhaitez pas les rendre accessibles.
- Déployez le site derrière HTTPS et conservez les protections `.htaccess` sur un hébergement Apache.
- Sauvegardez régulièrement la base de données et protégez les accès à l’hébergement.

## Structure du projet

```text
.
├── admin/          # Espace d’administration
├── artefacts/      # Ressources et exemples liés aux projets
├── assets/         # Feuilles de style, scripts et images
├── database/       # Schéma SQL
├── documents/      # CV et documents publics
├── en/             # Pages en anglais
├── includes/       # Configuration, fonctions et composants partagés
├── projects/       # Pages détaillées des projets
└── publications/   # Publications
```

## Licence

Le fichier `composer.json` indique une licence propriétaire. Aucune licence libre de réutilisation du code n’est accordée par ce dépôt.
