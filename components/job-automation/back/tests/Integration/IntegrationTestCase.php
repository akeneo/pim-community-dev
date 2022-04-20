<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Test\Integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class IntegrationTestCase extends WebTestCase
{
    protected CatalogInterface $catalog;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['environment' => 'test', 'debug' => false]);

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service): ?object
    {
        return self::getContainer()->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    protected function logAs(string $username): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    protected function assertEntityIsValid(object $entity): void
    {
        $constraints = $this->get('validator')->validate($entity);
        $this->assertCount(0, $constraints, $constraints->__toString());
    }
}
