<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AbstractCopierTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Creates a product.
     *
     * @param string $sku
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return ProductInterface
     */
    protected function createProduct($sku, array $data)
    {
        $productUpdater = $this->get('pim_catalog.updater.product');

        $product = $this->get('pim_catalog.builder.product')->createProduct($sku);
        $productUpdater->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $product;
    }
}
