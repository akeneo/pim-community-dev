<?php

return [
    // Tests related bundles
    Acme\Bundle\AppBundle\AcmeAppBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
    Akeneo\Test\IntegrationTestsBundle\AkeneoIntegrationTestsBundle::class => ['dev' => true, 'test' => true],
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test_fake' => true],
];
