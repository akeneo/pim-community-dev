<?php

return [
    // Tests related bundles
    AcmeEnterprise\Bundle\AppBundle\AcmeEnterpriseAppBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
    Akeneo\Test\IntegrationTestsBundle\AkeneoIntegrationTestsBundle::class => ['test' => true, 'test_fake' => true],
    AkeneoEnterprise\Test\IntegrationTestsBundle\AkeneoEnterpriseIntegrationTestsBundle::class => ['test' => true, 'test_fake' => true],
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test_fake' => true],
];
