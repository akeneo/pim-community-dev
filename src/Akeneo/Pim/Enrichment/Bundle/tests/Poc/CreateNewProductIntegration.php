<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Repository\Sql\ProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product\Product;
use Pim\Component\Catalog\Model\ValueCollection;

class CreateNewProductIntegration extends TestCase
{
    public function test_product_creation()
    {
        $connection = $this->get('database_connection');
        //$connection->setNestTransactionsWithSavepoints(true);
        //$connection->beginTransaction();

        $repository = $this->get('pim_enrichment.massive_operation.product_repository');
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_localized_and_scopable_text_area');

        $scalarValues = [];
        for ($i=0; $i<600;$i++) {
            $scalarValues[] = new ScalarValue(
                $attribute,
                'ecommerce',
                 $i . 'en_US',
                'text'
            );
        }
        $values = new ValueCollection($scalarValues);

        $start = microtime(true);
        for ($i=0; $i<1000;$i++) {
            $product = new Product('identifier'.$i, ['master', 'categoryA'], $values);
            $repository->persist($product);
        }

        //$connection->commit();

        echo 'commit : '. ProductRepository::$total .PHP_EOL;
        echo 'create : '. ProductRepository::$createProduct .PHP_EOL;
        echo 'normalize values : '. ProductRepository::$normalizeValues .PHP_EOL;
        echo 'persist values : '. ProductRepository::$persistValues .PHP_EOL;
        echo 'total time : '. (microtime(true) - $start) .PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
