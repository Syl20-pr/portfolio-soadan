<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translate.php';

$personal = [
    'full_name' => 'Sylvain Koffi SOADAN',
    'headline' => 'African Digital Governance & AI Policy',
    'summary' => 'Professional profile in English.',
    'email' => 'sylvain@example.test',
    'phone' => '+228 00 00 00 00',
    'location' => 'Lomé, Togo',
    'website' => '',
    'linkedin' => '',
    'nationality' => 'Togolese',
];
$source = ['full_name' => 'Sylvain Koffi SOADAN'];
$data = [
    'summary' => ['text' => 'Professional profile in English.'],
    'experience' => ['items' => [['org_fr' => 'Organisation', 'org_en' => 'Organisation']]],
    'education' => ['items' => []],
    'certifications' => ['items' => []],
    'publications' => ['items' => []],
    'associations' => ['items' => []],
    'distinctions' => ['items' => []],
    'languages' => ['items' => []],
    'skills' => ['items' => [['name' => 'Gouvernance numérique', 'name_en' => 'Digital governance']]],
];

$result = cv_en_autofill($personal, $data, $source);
if (!$result['ok'] || $result['translated'] !== 0
    || $data['skills']['items'][0]['name'] !== 'Digital governance') {
    fwrite(STDERR, "Manual English fields should render without automatic translation.\n");
    exit(1);
}

$GLOBALS['CV_TRANSLATOR'] = static function (array $batch): array {
    throw new RuntimeException('not_configured');
};
$data['skills']['items'][0]['name_en'] = '';
$result = cv_en_autofill($personal, $data, $source);
if ($result['ok'] || $result['error'] !== 'not_configured') {
    fwrite(STDERR, "Missing English fields should still report an unavailable translator.\n");
    exit(1);
}

echo "Manual English translation: OK\n";
