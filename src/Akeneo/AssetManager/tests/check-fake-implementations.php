<?php

declare(strict_types=1);

function writeln($text, $type = 'normal'): void
{
    $RESET = "\e[0m";
    $BOLD_WHITE = "\033[1;38m";
    $GREEN = "\033[0;32m";
    $YELLOW = "\033[0;33m";
    $BG_RED = "\033[41m";

    switch ($type) {
        case 'info':
            echo $GREEN . $text;
            break;
        case 'error':
            echo $BOLD_WHITE . $BG_RED . $text;
            break;
        case 'comment':
            echo $YELLOW . $text;
            break;
        default:
            echo $RESET . $text;
            break;
    }

    echo PHP_EOL;
}

function openFileFromRoot($fileName): string
{
    $ROOT = __DIR__ . '/../../../../';

    return file_get_contents($ROOT . $fileName);
}

$REFERENCE_FILE = 'src/Akeneo/AssetManager/back/Infrastructure/Symfony/Resources/config/persistence.yml';

$FILES_TO_CHECK = [
    'src/Akeneo/AssetManager/tests/back/Common/Resources/fake_services.yml',
];

$referenceContent = openFileFromRoot($REFERENCE_FILE);

$pattern = '/akeneo_assetmanager.infrastructure.persistence.(query|repository)\.(.+):/';
$matches = [];
preg_match_all($pattern, $referenceContent, $matches);

$matches = isset($matches[0]) ? $matches[0] : [];
$missings = [];

foreach ($FILES_TO_CHECK as $fileToCheck) {
    $contentToCheck = openFileFromRoot($fileToCheck);

    foreach ($matches as $match) {
        if (false === strpos($contentToCheck, $match)) {
            $missings[$match][] = $fileToCheck;
        }
    }
}

if (empty($missings)) {
    writeln('ALL SERVICES OK', 'info');

    return;
}

writeln(sprintf('%s SERVICES MISSING!', count($missings)), 'error');
echo "\n";

foreach ($missings as $serviceName => $fileNames) {
    writeln(sprintf('%s', $serviceName), 'comment');
    foreach ($fileNames as $fileName) {
        writeln(sprintf("- %s", $fileName));
    }

    echo PHP_EOL;
}

$exitCode = empty($missings) ? 0 : 1;
exit($exitCode);
