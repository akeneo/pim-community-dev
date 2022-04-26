<?php

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
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
            ->in(__DIR__ . '/..')
            ->exclude([
                // @fixme PHP CS issues and remove excluded infrastructure folders step by step
                'Infrastructure/Component',
                'Infrastructure/Controller',
                'Infrastructure/Doctrine',
                'Infrastructure/Elasticsearch',
                'Infrastructure/EventSubscriber',
                'Infrastructure/Form',
                'Infrastructure/Storage',
                'Infrastructure/Symfony',
                'Infrastructure/Twig',
            ])
    );
