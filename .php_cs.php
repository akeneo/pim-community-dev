<?php

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@PSR2' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline'
        ],
    ))
    ->setCacheFile('var/php_cs.cache')
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->notName('*Spec.php')
            ->notName('*Integration.php')
            ->in(__DIR__ . '/tests/legacy/features')
            ->in(__DIR__ . '/tests/features')
            ->in(__DIR__ . '/tests/back/Acceptance')
            ->in(__DIR__ . '/src')
            ->exclude([
                // There is a php file in the node_modules directory of these workspaces:
                'Oro/Bundle/ConfigBundle/front',
                'Akeneo/Platform/Bundle/CatalogVolumeMonitoringBundle/front',
                'Akeneo/Platform/Job/front',
            ])
    );
