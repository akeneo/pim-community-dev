<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration;

use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the Preview Generators.
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
abstract class PreviewGeneratorIntegrationTestCase extends KernelTestCase
{
    protected const IMAGE_FILENAME = '2016/04/Fred-site-web.jpg';
    protected const DOCUMENT_FILENAME = '2016/04/1_4_user_guide.pdf';

    protected FixturesLoader $fixturesLoader;

    private CacheManager $cacheManager;

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->fixturesLoader = $this->get('akeneoasset_manager.tests.helper.fixtures_loader');
        $this->resetDB();
    }

    public function tearDown(): void
    {
        $this->cacheManager = $this->get('liip_imagine.cache.manager');
        $this->cacheManager->remove();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    protected function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}
