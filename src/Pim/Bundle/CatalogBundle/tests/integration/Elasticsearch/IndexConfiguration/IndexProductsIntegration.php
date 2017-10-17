<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * Integration tests on product mapping
 *
 * @author    Benoit Jacquemont <benoit.jacqumont@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pim_catalog_product';

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
    protected function setUp()
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
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

        $this->esProductClient->index(self::DOCUMENT_TYPE, $productWithDateInText['identifier'], $productWithDateInText);


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

        $this->esProductClient->index(self::DOCUMENT_TYPE, $productWithPureText['identifier'], $productWithPureText);
    }
}
