<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Contenu commun du <head>
 * =============================================================================
 *  Regroupe les pages du portfolio via un identifiant et un contexte.
 *  Aucune logique de mise en page n'est dupliquée : header.php et footer.php
 *  restent la source unique du balisage partagé.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'mailer.php';

/**
 * Métadonnées par page et par langue (reprises telles quelles des pages HTML).
 *
 * @param string $page
 * @param string $lang
 * @return array{0:string,1:string}
 */
function page_meta(string $page, string $lang): array
{
    $meta = [
        'fr' => [
            'index' => ['SOADAN Koffi Sylvain — African Digital Governance & AI Policy', "Portfolio professionnel de SOADAN Koffi Sylvain — gouvernance numérique africaine, politiques de l'IA, cybersécurité, droit public, sciences politiques et diplomatie technologique."],
            'about' => ['À propos — SOADAN Koffi Sylvain | SKS', "Portrait de SOADAN Koffi Sylvain : parcours, engagement, valeurs et vision d'un étudiant togolais à la croisée du développement logiciel, du droit public, des sciences politiques et de la diplomatie numérique."],
            'education' => ['Formation — SOADAN Koffi Sylvain | SKS', "Formation de SOADAN Koffi Sylvain : génie logiciel et systèmes d'information (IAI-Togo), droit public (Université de Lomé), sciences politiques (Université de Kara)."],
            'experience' => ['Expérience — SOADAN Koffi Sylvain | SKS', "Expériences et engagements de SOADAN Koffi Sylvain en technologie, droit public, politiques publiques et relations internationales."],
            'expertise' => ['Expertise — SOADAN Koffi Sylvain | SKS', "Huit domaines d'expertise : gouvernance de l'IA, gouvernance numérique, cybersécurité, gouvernance des données, droit public, politiques publiques, diplomatie technologique, génie logiciel."],
            'projects' => ['Projets — SOADAN Koffi Sylvain | SKS', "Projets documentés de SOADAN Koffi Sylvain en gouvernance du numérique : BITC, African Health OS, 4 Voix Jeunesse, TJT, JDA et African Digital Sovereignty Observatory."],
            'research' => ['Recherche — SOADAN Koffi Sylvain | SKS', "Recherche et publications de SOADAN Koffi Sylvain sur la gouvernance numérique africaine, la souveraineté des données et les politiques de l'IA."],
            'certifications' => ['Certifications — SOADAN Koffi Sylvain | SKS', "Certifications et formations complémentaires de SOADAN Koffi Sylvain."],
            'leadership' => ['Leadership — SOADAN Koffi Sylvain | SKS', "Leadership et engagements bénévoles de SOADAN Koffi Sylvain."],
            'dossier' => ['Dossier professionnel — SOADAN Koffi Sylvain | SKS', "Dossier professionnel complet de SOADAN Koffi Sylvain : profil, formation, expérience, leadership, expertises, projets, recherche, certifications et documents."],
            'contact' => ['Contact — SOADAN Koffi Sylvain | SKS', "Contact institutionnel de SOADAN Koffi Sylvain — stages, bourses, collaborations de recherche, missions et opportunités internationales en gouvernance numérique et politiques de l'IA."],
            'thanks' => ['Message envoyé — SOADAN Koffi Sylvain | SKS', ''],
        ],
        'en' => [
            'index' => ['SOADAN Koffi Sylvain — African Digital Governance & AI Policy', "Professional portfolio of SOADAN Koffi Sylvain — African digital governance, AI policy, cybersecurity, public law, political science and technology diplomacy."],
            'about' => ['About — SOADAN Koffi Sylvain | SKS', "Profile of SOADAN Koffi Sylvain: journey, engagement, values and vision of a Togolese student working across software engineering, public law, political science and digital diplomacy."],
            'education' => ['Education — SOADAN Koffi Sylvain | SKS', "Education of SOADAN Koffi Sylvain: software engineering and information systems (IAI-Togo), public law (University of Lomé), political science (University of Kara)."],
            'experience' => ['Experience — SOADAN Koffi Sylvain | SKS', "Experience and engagements of SOADAN Koffi Sylvain in technology, public law, public policy and international affairs."],
            'expertise' => ['Expertise — SOADAN Koffi Sylvain | SKS', "Eight fields of expertise: AI governance, digital governance, cybersecurity, data governance, public law, public policy, technology diplomacy, software engineering."],
            'projects' => ['Projects — SOADAN Koffi Sylvain | SKS', "Documented projects by SOADAN Koffi Sylvain in digital governance: BITC, African Health OS, 4 Voix Jeunesse, TJT, JDA and the African Digital Sovereignty Observatory."],
            'research' => ['Research — SOADAN Koffi Sylvain | SKS', "Research and publications by SOADAN Koffi Sylvain on African digital governance, data sovereignty and AI policy."],
            'certifications' => ['Certifications — SOADAN Koffi Sylvain | SKS', "Certifications and further training of SOADAN Koffi Sylvain."],
            'leadership' => ['Leadership — SOADAN Koffi Sylvain | SKS', "Leadership and volunteer engagements of SOADAN Koffi Sylvain."],
            'dossier' => ['Professional Dossier — SOADAN Koffi Sylvain | SKS', "Complete professional dossier of SOADAN Koffi Sylvain: profile, education, experience, leadership, expertise, projects, research, certifications and documents."],
            'contact' => ['Contact — SOADAN Koffi Sylvain | SKS', "Institutional contact for SOADAN Koffi Sylvain — internships, fellowships, research collaborations and international opportunities in digital governance and AI policy."],
            'thanks' => ['Message sent — SOADAN Koffi Sylvain | SKS', ''],
        ],
    ];

    if (isset($meta[$lang][$page])) {
        return $meta[$lang][$page];
    }
    return ['SOADAN Koffi Sylvain | SKS', ''];
}
