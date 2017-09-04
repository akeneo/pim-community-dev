<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductModelIntegration extends TestCase
{
    /**
     * Ensure that the parent of a product model cannot be changed.
     *
     * TODO: This will become possible in PIM-6350.
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException
     * @expectedExceptionMessage Property "parent" cannot be modified, "amor" given.
     */
    public function testTheParentCannotBeChanged(): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['parent' => 'amor',]);
    }

    /**
     * Ensure that the family variant of a product model cannot be changed.
     *
     * TODO: This will become possible in PIM-6344.
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException
     * @expectedExceptionMessage Property "family_variant" cannot be modified, "shoes_size" given.
     */
    public function testTheFamilyVariantCannotBeChanged(): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('apollon_blue');
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['family_variant' => 'shoes_size',]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }
}
