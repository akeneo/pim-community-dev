<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\tests\integration\ElasticSearch\IndexConfiguration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IndexPublishedProductsIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pimee_workflow_published_product';

    /** @var Client */
    protected $esPublishedProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esPublishedProductClient = $this->get('akeneo_elasticsearch.client.product');
    }

    public function testDateBeforeText(): void
    {
        $productWithDateInText = [
            'identifier' => 'product_1',
            'values' => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2015/01/01',
                    ],
                ],
            ],
        ];

        $this->esPublishedProductClient->index(
            self::DOCUMENT_TYPE,
            $productWithDateInText['identifier'],
            $productWithDateInText
        );

        $productWithPureText = [
            'identifier' => 'product_2',
            'values' => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'My nice product',
                    ],
                ],
            ],
        ];

        $this->esPublishedProductClient->index(
            self::DOCUMENT_TYPE,
            $productWithPureText['identifier'],
            $productWithPureText
        );
    }
}
