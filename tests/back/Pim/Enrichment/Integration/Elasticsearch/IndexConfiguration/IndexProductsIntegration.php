<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * Integration tests on product mapping
 *
 * @author    Benoit Jacquemont <benoit.jacqumont@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsIntegration extends TestCase
{
    /** @var Client */
    protected $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testDateBeforeText()
    {
        $productWithDateInText = [
            'identifier' => 'product_1',
            'values'     => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2015/01/01',
                    ],
                ],
            ]
        ];

        $this->esProductClient->index($productWithDateInText['identifier'], $productWithDateInText);


        $productWithPureText = [
            'identifier' => 'product_2',
            'values'     => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'My nice product',
                    ],
                ],
            ]
        ];

        $this->esProductClient->index($productWithPureText['identifier'], $productWithPureText);
    }
}
