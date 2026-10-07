<?php
/**
 * Page générée automatiquement depuis la version HTML statique.
 * Le design d'origine est intégralement conservé ; seuls l'en-tête
 * et le pied de page sont factorisés via includes/.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/page.php';

$LANG = 'fr';
$PAGE = 'about';
$BASE = '';
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];
$DESCRIPTION = $META[1];

require __DIR__ . '/includes/header.php';
?>


  <main id="main">

    <section class="page-hero">
      <div class="container">
        <p class="kicker">À propos</p>
        <h1>Un parcours construit entre la machine, la norme et la décision publique.</h1>
        <p class="lead">Je m'appelle Koffi Sylvain SOADAN. J'ai grandi à Lomé, j'apprends à écrire du logiciel, et je
          m'intéresse à la manière dont une société décide de ce que la technologie a le droit de faire.</p>
      </div>
    </section>

    <nav class="about-nav" aria-label="Sections de la page">
      <div class="container">
        <a href="#qui-je-suis" data-jump>Qui je suis</a>
        <a href="#parcours" data-jump>Parcours</a>
        <a href="#experiences" data-jump>Expériences</a>
        <a href="#ce-qui-me-definit" data-jump>Ce qui me définit</a>
        <a href="#vision" data-jump>Vision</a>
        <a href="#valeurs" data-jump>Valeurs</a>
      </div>
    </nav>

    <!-- ============================================================ Qui je suis -->
    <section class="section" id="qui-je-suis" aria-labelledby="identity-title">
      <div class="container identity-grid" data-reveal>
        <figure class="identity-portrait">
          <img src="assets/images/photo1.jpeg" alt="Portrait de SOADAN Koffi Sylvain" loading="lazy">
          <figcaption>SOADAN Koffi Sylvain · Lomé, Togo</figcaption>
        </figure>

        <div class="identity-intro">
          <p class="eyebrow">Qui je suis</p>
          <h2 id="identity-title" class="section-title">Comment fonctionne un système, et comment une société décide
            de l'encadrer.</h2>

          <p class="lead-strong">Je cherche à comprendre deux choses en même temps : comment fonctionne un système
            technique, et comment une société décide de ce que ce système a le droit de faire.</p>

          <p>L'informatique m'a attiré par les objets avant les discours. Le premier programme qui fonctionne, la
            première base de données qu'on modélise correctement, la première fois qu'un réseau répond : c'est là que
            j'ai compris que je voulais construire, et pas seulement commenter. Depuis, je poursuis une licence en
            <strong>génie logiciel et systèmes d'information à l'IAI-Togo</strong>, où j'apprends l'algorithmique,
            l'architecture, les bases de données et les réseaux.</p>

          <p>Assez vite, une question est revenue : qui décide de ce qu'un système a le droit de faire avec les données
            d'un citoyen ? Cette question m'a conduit vers le <strong>droit public à l'Université de Lomé</strong>,
            puis vers les <strong>sciences politiques à l'Université de Kara</strong>. Je ne suis pas ces trois
            formations en parallèle par goût de la collection. Je les suis parce que la gouvernance de l'intelligence
            artificielle, la protection des données ou la cybersécurité ne se traitent correctement ni par un ingénieur
            seul, ni par un juriste seul, ni par un politiste seul.</p>

          <p>Ce qui m'intéresse, en un mot, c'est la <strong>diplomatie numérique</strong> : comprendre comment des
            États africains négocient, adoptent et appliquent des règles communes sur des technologies qu'ils
            n'hébergent pas toujours. C'est un terrain où il faut parler à la fois protocole réseau et protocole de
            négociation, et c'est précisément là que je veux construire ma place.</p>

          <p>Je documente ce travail en public, sans exagérer son avancement : des spécifications, des prototypes, des
            notes d'analyse et du code. Ce portfolio est un carnet de progression autant qu'une vitrine.</p>
        </div>
      </div>
    </section>

    <!-- =============================================================== Parcours -->
    <section class="section section-soft" id="parcours" aria-labelledby="journey-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">Mon parcours</p>
          <h2 id="journey-title" class="section-title">Les étapes, dans l'ordre où elles sont arrivées.</h2>
          <p class="section-lead">Un parcours commence souvent par un hasard. Voici les moments qui ont compté, et ce
            que j'en ai retenu.</p>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2022</p>
          <h3>Croix-Rouge Togolaise</h3>
          <p class="stage-place">Bénévole et secouriste</p>
          <p class="stage-text">Le premier engagement qui m'a appris qu'une compétence n'a de valeur que lorsqu'elle
            sert à quelqu'un. Formation aux premiers secours, présence sur le terrain, discipline des gestes.</p>
          <ul class="stage-skills">
            <li>Premiers secours</li>
            <li>Travail en équipe</li>
            <li>Engagement citoyen</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2023 à 2025</p>
          <h3>Lycée Agoè-Centre</h3>
          <p class="stage-place">Responsable étudiant, clubs scientifique, santé et environnement</p>
          <p class="stage-text">Prendre la parole au nom des élèves, organiser, convaincre. C'est là que j'ai touché
            pour la première fois à l'Arduino et à la robotique, et que la programmation est passée du cours au projet.
          </p>
          <ul class="stage-skills">
            <li>Arduino</li>
            <li>Robotique</li>
            <li>Prise de parole</li>
            <li>Organisation d'activités</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2024 à 2025</p>
          <h3>Baccalauréat série C4</h3>
          <p class="stage-place">Enseignement secondaire, Togo</p>
          <p class="stage-text">Une formation scientifique exigeante qui m'a donné la base logique de l'ingénierie, et
            l'habitude de travailler un problème jusqu'à ce qu'il cède.</p>
          <ul class="stage-skills">
            <li>Mathématiques</li>
            <li>Méthode scientifique</li>
            <li>Rigueur</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 à ce jour</p>
          <h3>Génie logiciel et systèmes d'information</h3>
          <p class="stage-place">Institut Africain d'Informatique (IAI-Togo)</p>
          <p class="stage-text">Deuxième année. Développement logiciel, programmation, bases de données relationnelles,
            réseaux et technologies numériques. C'est la colonne technique de tout le reste, et la partie de mon
            parcours qui me demande le plus de travail quotidien.</p>
          <ul class="stage-skills">
            <li>Python, Java, C</li>
            <li>Django, React</li>
            <li>SQL</li>
            <li>Réseaux TCP/IP</li>
            <li>Modélisation</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 à ce jour</p>
          <h3>Enseigner la programmation, l'IA et la cybersécurité</h3>
          <p class="stage-place">iBase, Togo</p>
          <p class="stage-text">Accompagner de jeunes apprenants m'a forcé à clarifier ce que je croyais savoir. On ne
            comprend vraiment un concept qu'au moment où l'on doit l'expliquer à quelqu'un qui n'a aucune raison de
            vous croire sur parole.</p>
          <ul class="stage-skills">
            <li>Pédagogie</li>
            <li>Vulgarisation technique</li>
            <li>Intelligence artificielle</li>
            <li>Cybersécurité</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 à ce jour</p>
          <h3>Santé des jeunes et représentation nationale</h3>
          <p class="stage-place">AIMES-Afrique / Health Corps / SOS Docteur TV Togo</p>
          <p class="stage-text">Représentation et coordination autour d'initiatives de santé des jeunes, en parallèle
            d'une mission de volontariat en ligne pour les Nations Unies sur les maladies tropicales négligées en
            Guinée-Conakry.</p>
          <ul class="stage-skills">
            <li>Plaidoyer</li>
            <li>Coordination</li>
            <li>Communication</li>
            <li>Santé publique</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2025 à ce jour</p>
          <h3>Compassion International Togo</h3>
          <p class="stage-place">Leadership jeunesse et plaidoyer communautaire</p>
          <p class="stage-text">Leadership communautaire, protection de l'environnement, et surtout une leçon sur la
            participation des jeunes à la décision locale : une politique ne change rien si personne ne se sent
            concerné par elle.</p>
          <ul class="stage-skills">
            <li>Leadership</li>
            <li>Décision locale</li>
            <li>Environnement</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 à ce jour</p>
          <h3>Droit public</h3>
          <p class="stage-place">Université de Lomé · Faculté de droit</p>
          <p class="stage-text">Là où j'apprends à lire un texte normatif, à distinguer une règle d'une intention, et à
            comprendre comment un État organise sa propre contrainte. C'est le pont entre ma technique et l'idée de
            gouvernance.</p>
          <ul class="stage-skills">
            <li>Droit constitutionnel</li>
            <li>Droit administratif</li>
            <li>Droit international public</li>
            <li>Libertés publiques</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 à ce jour</p>
          <h3>Sciences politiques</h3>
          <p class="stage-place">Université de Kara</p>
          <p class="stage-text">Institutions, pouvoir, politiques publiques, relations internationales et
            organisations multilatérales. C'est ici que ma curiosité pour les traités et les négociations est devenue
            un objet de travail.</p>
          <ul class="stage-skills">
            <li>Relations internationales</li>
            <li>Analyse des politiques publiques</li>
            <li>Géopolitique africaine</li>
            <li>Organisations multilatérales</li>
          </ul>
        </div>

        <div class="timeline-stage">
          <p class="stage-date">2026 à ce jour</p>
          <h3>Coopération internationale et diplomatie numérique</h3>
          <p class="stage-place">MoNaJeL Togo · bureau des étudiants de l'IAI-Togo · réseau étudiant soutenu par
            l'Ambassade des États-Unis</p>
          <p class="stage-text">Conseiller général au MoNaJeL sur les questions de jeunesse et de coopération
            internationale, conseiller général au bureau des étudiants de l'IAI-Togo, délégué étudiant dans un réseau
            soutenu par l'Ambassade des États-Unis. Trois endroits différents pour la même question : comment une
            institution africaine se relie au reste du monde.</p>
          <ul class="stage-skills">
            <li>Diplomatie numérique</li>
            <li>Coopération internationale</li>
            <li>Animation de réseaux</li>
            <li>Gouvernance institutionnelle</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- ============================================================ Expériences -->
    <section class="section" id="experiences" aria-labelledby="exp-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">Mes expériences</p>
          <h2 id="exp-title" class="section-title">Ce que j'ai réellement fait.</h2>
          <p class="section-lead">Chaque fonction est indiquée telle qu'elle a été exercée, sans responsabilité
            élargie. Les expériences détaillées s'ouvrent au clic.</p>
        </div>

        <div class="grid-2">
          <article class="exp-card" data-reveal>
            <h3>Formateur en programmation, IA et cybersécurité</h3>
            <p class="exp-org">iBase · Togo · actuel</p>
            <p class="exp-summary">Accompagnement de jeunes apprenants sur la programmation, l'intelligence
              artificielle et la cybersécurité.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-ibase">Voir le détail</button>
            <div class="exp-detail" id="exp-ibase" hidden>
              <p>Le travail consiste à rendre accessibles des notions souvent présentées de façon abstraite. J'adapte
                le rythme, je corrige des exercices pratiques et je présente les bases de la sécurité informatique de
                manière concrète.</p>
              <ul>
                <li>Animation d'ateliers de programmation</li>
                <li>Initiation à l'intelligence artificielle</li>
                <li>Fondamentaux de la cybersécurité</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Conseiller général</h3>
            <p class="exp-org">MoNaJeL Togo · 2026 à ce jour</p>
            <p class="exp-summary">Appui aux initiatives de jeunesse, de coopération internationale, de leadership et
              de mobilisation.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-monajel">Voir le détail</button>
            <div class="exp-detail" id="exp-monajel" hidden>
              <p>Le Mouvement National des Jeunes Leaders pour la Coopération Internationale travaille sur le lien
                entre jeunesse et ouverture internationale. J'appuie la préparation d'activités et la mobilisation des
                jeunes autour de ces questions.</p>
              <ul>
                <li>Appui aux initiatives de jeunesse</li>
                <li>Contribution aux échanges de coopération internationale</li>
                <li>Animation et mobilisation</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Représentant national</h3>
            <p class="exp-org">AIMES-Afrique / Health Corps / SOS Docteur TV Togo · 2025 à ce jour</p>
            <p class="exp-summary">Représentation et coordination autour des initiatives de santé des jeunes.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-aimes">Voir le détail</button>
            <div class="exp-detail" id="exp-aimes" hidden>
              <p>Sensibilisation sur la santé des jeunes et représentation de ces initiatives auprès d'autres acteurs,
                en lien avec les activités de SOS Docteur TV.</p>
              <ul>
                <li>Représentation auprès de partenaires</li>
                <li>Coordination de campagnes de sensibilisation</li>
                <li>Communication sur la santé publique</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Conseiller général, bureau des étudiants</h3>
            <p class="exp-org">IAI-Togo · 2026 à ce jour</p>
            <p class="exp-summary">Promotion des projets étudiants, de la programmation et des ateliers techniques.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-iai">Voir le détail</button>
            <div class="exp-detail" id="exp-iai" hidden>
              <p>Au sein du bureau des étudiants, je pousse les projets collectifs et les ateliers techniques, pour que
                la programmation reste une pratique et non une simple matière à réviser.</p>
              <ul>
                <li>Promotion des projets étudiants</li>
                <li>Organisation d'ateliers techniques</li>
                <li>Animation de la vie associative</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Délégué étudiant et administrateur de groupe</h3>
            <p class="exp-org">Réseau étudiant soutenu par l'Ambassade des États-Unis · 2026 à ce jour</p>
            <p class="exp-summary">Coordination de l'information et animation d'activités numériques.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-us">Voir le détail</button>
            <div class="exp-detail" id="exp-us" hidden>
              <p>Un réseau d'étudiants qui échangent des informations et participent à des activités numériques.
                J'assure la circulation de l'information et l'organisation de certaines activités.</p>
              <ul>
                <li>Coordination de l'information</li>
                <li>Animation d'activités numériques</li>
                <li>Communication entre étudiants</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Responsable étudiant et clubs scientifique, santé et environnement</h3>
            <p class="exp-org">Lycée Agoè-Centre · Togo · 2023 à 2025</p>
            <p class="exp-summary">Prise de parole au nom des élèves, activités Arduino et robotique,
              sensibilisation.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-lycee">Voir le détail</button>
            <div class="exp-detail" id="exp-lycee" hidden>
              <p>Un poste qui m'a appris à représenter un groupe, à défendre une proposition devant une direction et à
                organiser des activités avec peu de moyens.</p>
              <ul>
                <li>Représentation des élèves</li>
                <li>Ateliers Arduino et robotique</li>
                <li>Sensibilisation santé et environnement</li>
                <li>Plantation d'arbres</li>
              </ul>
            </div>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Bénévole et secouriste</h3>
            <p class="exp-org">Croix-Rouge Togolaise · 2022 à ce jour</p>
            <p class="exp-summary">Engagement bénévole et formation aux premiers secours.</p>
          </article>

          <article class="exp-card" data-reveal>
            <h3>Volontaire en ligne</h3>
            <p class="exp-org">UN Volunteers · Nations Unies</p>
            <p class="exp-summary">Communication et sensibilisation sur les maladies tropicales négligées.</p>
            <button class="exp-toggle" type="button" data-expand aria-expanded="false"
              aria-controls="exp-unv">Voir le détail</button>
            <div class="exp-detail" id="exp-unv" hidden>
              <p>Mission de volontariat en ligne portant sur la communication et la sensibilisation autour des
                maladies tropicales négligées en Guinée-Conakry, avec un focus sur les communautés rurales d'Afrique de
                l'Ouest.</p>
              <ul>
                <li>Communication</li>
                <li>Sensibilisation santé</li>
                <li>Contexte ouest-africain</li>
              </ul>
            </div>
          </article>
        </div>

        <p class="mt-4 mb-0"><a class="btn btn-secondary" href="experience.php">Voir la chronologie complète</a></p>
      </div>
    </section>

    <!-- ==================================================== Ce qui me définit -->
    <section class="section section-soft" id="ce-qui-me-definit" aria-labelledby="pillars-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">Ce qui me définit</p>
          <h2 id="pillars-title" class="section-title">Cinq dimensions, un seul profil.</h2>
          <p class="section-lead">Mon profil n'est pas dispersé : il est construit sur cinq axes qui se répondent.</p>
        </div>

        <div class="pillar-grid" data-reveal>
          <article class="pillar">
            <span class="pillar-label">Technologie</span>
            <p>Développement logiciel, intelligence artificielle, cybersécurité, systèmes d'information.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Gouvernance</span>
            <p>Droit public, politiques publiques, gouvernance numérique, gouvernance de l'IA.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Diplomatie</span>
            <p>Relations internationales, diplomatie numérique, coopération internationale.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Engagement</span>
            <p>Jeunesse, volontariat, développement communautaire et engagement citoyen.</p>
          </article>
          <article class="pillar">
            <span class="pillar-label">Afrique</span>
            <p>Innovation, transformation numérique et développement du continent.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ================================================================= Vision -->
    <section class="section" id="vision" aria-labelledby="vision-title">
      <div class="container grid-2" data-reveal>
        <div>
          <p class="eyebrow">Ma vision</p>
          <h2 id="vision-title" class="section-title">La technique n'est qu'une moitié du problème.</h2>
        </div>
        <div class="prose">
          <blockquote class="pull-quote">Une infrastructure sans règle reste fragile. Une règle sans infrastructure
            reste théorique.</blockquote>

          <p>Mon ambition tient en une phrase : construire un parcours à l'intersection de la <strong>technologie, du
              droit, de la politique, de la diplomatie et du développement africain</strong>. Je ne cherche pas à
            choisir entre ces mondes, parce que les problèmes que je veux traiter refusent cette séparation.</p>

          <p>Quand un État africain veut héberger les données de santé de ses citoyens, il n'y a pas un seul obstacle
            technique. Il y a une question de norme, une question de budget, une question de souveraineté et une
            question de négociation avec ses partenaires. Un pays qui n'a pas d'ingénieurs capables de concevoir le
            système négocie mal. Un pays qui a les ingénieurs mais pas les juristes le déploie mal. Et un pays qui a les
            deux mais ne sait pas négocier se le fait imposer.</p>

          <p>C'est cette chaîne que je veux comprendre, du protocole jusqu'au traité. Ma conviction est que la
            technologie africaine ne se joue pas uniquement dans les salles serveurs : elle se joue aussi dans les
            commissions, les conventions et les résolutions. La Convention de Malabo, l'Agenda 2063 et la stratégie
            continentale sur l'intelligence artificielle ne sont pas des textes décoratifs. Ce sont des instruments, et
            ils ont besoin de gens capables de les traduire dans le code.</p>

          <p>À long terme, je veux contribuer à la conception de cadres de gouvernance de l'IA, de politiques de
            cybersécurité et d'infrastructures publiques numériques pensés pour les réalités du continent, et
            défendables dans les enceintes où l'Afrique parle au monde. Trois capacités me semblent nécessaires : une
            expertise technique solide, une capacité juridique et normative, une aptitude au travail multilatéral. Je
            les construis une par une, en le disant clairement.</p>

          <p class="note"><strong>Transparence :</strong> les informations de ce portfolio correspondent uniquement à
            des formations, engagements et projets réellement en cours. Lorsqu'une information n'est pas disponible,
            elle est signalée comme telle plutôt qu'inventée. Aucun de mes projets n'est présenté comme déployé.</p>
        </div>
      </div>
    </section>

    <!-- =============================================================== Valeurs -->
    <section class="section section-soft" id="valeurs" aria-labelledby="values-title">
      <div class="container">
        <div class="section-head" data-reveal>
          <p class="eyebrow">Mes valeurs</p>
          <h2 id="values-title" class="section-title">Ce que j'essaie de ne pas négocier.</h2>
          <p class="section-lead">Des principes que j'ai appris en travaillant, pas une liste choisie par confort.</p>
        </div>

        <ul class="value-list" data-reveal>
          <li>
            <span class="value-key">Intégrité</span>
            <span class="value-text">Ne pas gonfler un résultat pour qu'il paraisse plus avancé qu'il ne l'est. Dire
              quand un projet est un concept et non un produit.</span>
          </li>
          <li>
            <span class="value-key">Service</span>
            <span class="value-text">Une compétence qui ne sert personne n'est qu'un ornement. C'est la Croix-Rouge qui
              me l'a appris avant tout le reste.</span>
          </li>
          <li>
            <span class="value-key">Rigueur</span>
            <span class="value-text">Un code se relit, une norme se cite, un chiffre se vérifie. Le détail n'est pas
              une coquetterie.</span>
          </li>
          <li>
            <span class="value-key">Apprentissage</span>
            <span class="value-text">Je préfère reconnaître ce que je ne sais pas encore et travailler dessus plutôt
              que d'en parler comme si je le maîtrisais.</span>
          </li>
          <li>
            <span class="value-key">Coopération</span>
            <span class="value-text">Les problèmes que je veux traiter dépassent un individu et souvent un seul pays.
              Travailler avec d'autres n'est pas un effort, c'est la méthode.</span>
          </li>
          <li>
            <span class="value-key">Responsabilité</span>
            <span class="value-text">Un système qu'on construit engage ceux qui l'utilisent. Concevoir, c'est déjà
              décider pour quelqu'un d'autre.</span>
          </li>
          <li>
            <span class="value-key">Ancrage africain</span>
            <span class="value-text">Partir des réalités locales, des langues, des institutions et des contraintes
              d'ici, plutôt que d'importer un modèle déjà fini.</span>
          </li>
          <li>
            <span class="value-key">Leadership</span>
            <span class="value-text">Prendre la parole au nom d'un groupe, assumer la décision, et laisser la place
              quand c'est le moment.</span>
          </li>
          <li>
            <span class="value-key">Impact</span>
            <span class="value-text">Je juge un projet à ce qu'il change concrètement, pas au nombre de personnes qu'il
              impressionne.</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- ============================================================ Références -->
    <section class="section" aria-labelledby="refs-title">
      <div class="container grid-2" data-reveal>
        <div>
          <p class="eyebrow">Pour aller plus loin</p>
          <h2 id="refs-title" class="section-title">Vérifier plutôt que croire.</h2>
          <p class="section-lead">Le détail de mon parcours est documenté page par page, avec les stades de chaque
            projet indiqués honnêtement.</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="projects.php">Voir mes projets</a>
            <a class="btn btn-secondary" href="contact.php">Me contacter</a>
          </div>
        </div>
        <div>
          <ul class="doc-list">
            <li><span class="doc-name">Formation</span><a class="link-inline" href="education.php">Trois cursus menés
                en parallèle</a></li>
            <li><span class="doc-name">Expérience</span><a class="link-inline" href="experience.php">Postes, mandats et
                engagements</a></li>
            <li><span class="doc-name">Expertise</span><a class="link-inline" href="expertise.php">Compétences et
                niveaux réels</a></li>
            <li><span class="doc-name">Leadership</span><a class="link-inline" href="leadership.php">Bénévolat et
                engagement citoyen</a></li>
          </ul>
        </div>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
