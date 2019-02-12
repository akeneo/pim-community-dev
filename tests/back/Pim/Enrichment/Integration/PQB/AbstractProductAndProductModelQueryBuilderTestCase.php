<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProductAndProductModelQueryBuilderTestCase extends TestCase
{
    /** @var Client */
    protected $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters): CursorInterface
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            [
                'default_locale' => 'en_US',
                'default_scope'  => 'ecommerce',
                'limit'          => 200, // set it big enough to have all products in one page
            ]
        );

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert(CursorInterface $result, array $expected): void
    {
        $entities = [];
        foreach ($result as $entity) {
            if ($entity instanceof ProductInterface) {
                $entities[] = $entity->getIdentifier();
            }

            if ($entity instanceof ProductModelInterface) {
                $entities[] = $entity->getCode();
            }
        }

        sort($entities);
        sort($expected);

        $this->assertSame($entities, $expected);
    }
}
