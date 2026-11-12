# Documentation hébergeur — Portfolio SKS (module multimédia, migration 004)

Ce document décrit les réglages serveur nécessaires aux nouvelles fonctions
multimédia : bandeau animé d'accueil, images d'articles/projets, rubrique
Actualités (vidéos, capsules, podcasts) et commentaires modérés.

## 1. Limites de téléversement (uploads vidéo/audio)

Les limites applicatives se règlent dans `.env` :

```
MB_MAX_UPLOAD_VIDEO=64    # Mo maximum par vidéo MP4
MB_MAX_UPLOAD_AUDIO=24    # Mo maximum par podcast MP3/M4A/OGG
```

**Important :** ces limites doivent rester inférieures aux limites PHP du
serveur, sinon l'upload échoue avant même nos contrôles. Dans le `php.ini`
(ou via l'outil « MultiPHP INI Editor » de cPanel) :

| Directive             | Valeur recommandée | Pourquoi                                              |
|-----------------------|--------------------|-------------------------------------------------------|
| `upload_max_filesize` | 70M ou plus        | doit dépasser `MB_MAX_UPLOAD_VIDEO`                   |
| `post_max_size`       | 80M ou plus        | doit dépasser `upload_max_filesize` (formulaire + fichier) |
| `max_execution_time`  | 120 minimum        | le téléversement de vidéo est lent sur connexion mobile |
| `max_input_time`      | 120 minimum        | idem, temps de réception côté serveur                 |
| `memory_limit`        | 256M               | finfo + déplacement du fichier, sans traitement vidéo |

Aucune transcodification n'est effectuée : le MP4/MP3 téléversé est servi tel
quel (voir §2). Pour des fichiers plus longs, utilisez une plateforme externe
(YouTube, Vimeo, Spotify…) via un **lien d'intégration** — c'est le mode
recommandé, il ne consomme ni disque ni bande passante chez l'hébergeur.

## 2. Sécurité du répertoire uploads/

* `uploads/.htaccess` interdit toute exécution PHP/CGI (`php_flag engine off`,
  `Require all denied` sur `*.php`, etc.) — il couvre aussi `uploads/media/`.
* Les fichiers reçus sont renommés aléatoirement (`includes/upload.php`,
  fonction `upload_handle()`), leur **type MIME réel** est vérifié par finfo
  (jamais l'extension déclarée par le navigateur). Seuls sont acceptés :
  JPEG/PNG/WebP (images), PDF (CV), MP4 (vidéo), MP3/M4A/OGG (audio).
* Si Apache est configuré avec `AllowOverride None`, ajoutez l'équivalent
  suivant à la configuration du vhost — la logique doit rester identique :

```apache
<Directory "/chemin/vers/portfolio/uploads">
    php_admin_flag engine off
    <FilesMatch "\.(php|phtml|phar|cgi|pl|py|sh)$">
        Require all denied
    </FilesMatch>
</Directory>
```

## 3. Content-Security-Policy (lecteurs externes)

La CSP est définie dans le `.htaccess` racine. Seuls les domaines des lecteurs
réellement autorisés (liste blanche `news_embed_hosts()` dans
`includes/content.php`) peuvent être encadrés en iframe :

```apache
Header always set Content-Security-Policy "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://www.vimeo.com https://player.vimeo.com https://www.facebook.com https://www.tiktok.com https://open.spotify.com https://embed.podcasts.apple.com https://w.soundcloud.com;"
```

La liste blanche applicative et cette CSP doivent évoluer ensemble : si vous
ajoutez un domaine dans `news_embed_hosts()`, ajoutez-le ici (et inversement).
Les lecteurs externes ne se chargent qu'**après un clic** de l'internaute
(click-to-load, page `news-item.php`) : aucune requête vers YouTube/Vimeo/etc.
n'a lieu au simple affichage de la page.

## 4. Drapeaux des États membres de l'ONU

Les 193 drapeaux sont des fichiers **SVG locaux** dans `assets/flags/`
(paquet [flag-icons](https://github.com/lipis/flag-icons) de lipis, licence
MIT — libres de droits, optimisés, aucune ressource externe). La liste des
États avec leurs noms français/anglais se trouve dans
`includes/un_members.php`. Rien à installer côté serveur.

## 5. Base de données

La migration `database/migration-004-multimedia.sql` est **idempotente** (à
importer une ou plusieurs fois sans erreur) et réversible via
`database/rollback-004-multimedia.sql`. Elle crée 4 tables (`media_images`,
`news_items`, `news_comments`, `comment_rate_limit`) et ajoute 3 colonnes à
`gallery_photos` sans toucher aux données existantes.

En développement sans MySQL : `php database/setup-sqlite.php` (crée ou met à
niveau la base SQLite, y compris les colonnes du bandeau).

## 6. Anti-spam des commentaires

Les commentaires sont **désactivés par défaut** (case par publication) et
**modérés avant publication** (statut `pending`). Trois protections :
1. jeton CSRF de session (`csrf_token`) ;
2. champ piège `website` (invisible, rempli uniquement par les robots) ;
3. limitation de fréquence : 3 commentaires / 10 minutes / IP
   (`comment_rate_limit`, IP hachée avec APP_KEY, jamais stockée en clair).
