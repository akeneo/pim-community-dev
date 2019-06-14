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

namespace Akeneo\ReferenceEntity\Integration;

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
    protected const DAM_URL = 'https://akeneodemo.getbynder.com/m/1e567bef001b08fa/';
    protected const FILENAME = 'Akeneo-DSC_2109-2.jpg';

    /** @var KernelInterface|null */
    protected $testKernel;

    /** @var CacheManager */
    private $cacheManager;

    public function setUp(): void
    {
        if (null === $this->testKernel) {
            $this->bootTestKernel();
        }
        $this->resetDB();
    }

    public function tearDown(): void
    {
        $this->cacheManager = $this->get('liip_imagine.cache.manager');
        $this->cacheManager->remove();
    }

    protected function bootTestKernel(): void
    {
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();
    }

    /*
     * @return mixed
     */
    protected function get(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }

    protected function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
