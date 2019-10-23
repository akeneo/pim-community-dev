<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration;

use Akeneo\AssetManager\Common\Fake\AssetIndexerSpy;
use Akeneo\AssetManager\Common\Fake\EventDispatcherMock;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the SQL implementation of query functions and repositories.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SqlIntegrationTestCase extends KernelTestCase
{
    /** @var FixturesLoader */
    protected $fixturesLoader;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->overrideContainer();
        $this->fixturesLoader = $this->get('akeneoasset_manager.tests.helper.fixtures_loader');
    }

    protected function get(string $service)
    {
        return self::$kernel->getContainer()->get($service);
    }

    protected function overrideContainer(): void
    {
        $realEventDispatcher = $this->get('event_dispatcher');
        self::$kernel->getContainer()->set('event_dispatcher', new EventDispatcherMock($realEventDispatcher));
        self::$kernel->getContainer()->set('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer', new AssetIndexerSpy());
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
}
