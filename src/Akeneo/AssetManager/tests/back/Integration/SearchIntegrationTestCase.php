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

namespace Akeneo\ReferenceEntity\Integration;

use Akeneo\ReferenceEntity\Integration\Persistence\Helper\SearchRecordIndexHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used for running integration tests testing the Search implementation of Elasticsearch queries or
 * custom filters.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SearchIntegrationTestCase extends KernelTestCase
{
    /** @var KernelInterface|null */
    protected $testKernel;

    /** @var SearchRecordIndexHelper */
    protected $searchRecordIndexHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (null === $this->testKernel) {
            $this->bootTestKernel();
        }
        $this->searchRecordIndexHelper = $this->get('akeneoreference_entity.tests.helper.search_index_helper');
        $this->searchRecordIndexHelper->resetIndex();
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
}
