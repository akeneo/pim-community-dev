<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class IntegrationTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['environment' => 'test', 'debug' => false]);

        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->purge();

        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function loadMinimalCatalog(): void
    {
        $catalog = self::getContainer()->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useMinimalCatalog();
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = self::getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    protected function logAs(string $username): void
    {
        $user = self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);
    }
}
