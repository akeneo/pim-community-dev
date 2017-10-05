<?php

namespace PimEnterprise\Bundle\VersioningBundle\tests\integration\Reverter;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use PimEnterprise\Bundle\VersioningBundle\Reverter\ProductReverter;
use PimEnterprise\Component\ActivityManager\Repository\ProductRepositoryInterface;

/**
 * Integration tests that ensure that a product is revertable to a prior version.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReverterIntegration extends TestCase
{
    public function testRevertProductValues()
    {
        $product = $this->getProductBuilder()->createProduct('versioned-product');
        $this->getProductSaver()->save($product);

        $updates = [
            'values' => [
                'a_number_integer' => [
                    ['locale' => null, 'scope' => null, 'data' => 2],
                ],
            ],
        ];
        $this->getProductUpdater()->update($product, $updates);
        $this->getProductSaver()->save($product);

        $productVersions = $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($product),
            $product->getId());
        $this->getProductReverter()->revert(end($productVersions));

        $productVersions = $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($product),
            $product->getId());
        $this->assertCount(3, $productVersions);

        $product = $this->getProductRepository()->findOneByIdentifier('versioned-product');

        $this->assertTrue($product->isEnabled());
        $this->assertNull($product->getFamily());
        $this->assertCount(0, $product->getCategories());
        $this->assertCount(1, $product->getValues());
        $this->assertNull($product->getValue('a_number_integer'));
    }

    public function testRevertProductFields()
    {
        $product = $this->getProductBuilder()->createProduct('versioned-product');
        $this->getProductSaver()->save($product);

        $updates = [
            'groups' => ['groupB'],
        ];
        $this->getProductUpdater()->update($product, $updates);
        $this->getProductSaver()->save($product);

        $productVersions = $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($product),
            $product->getId());
        $this->getProductReverter()->revert(end($productVersions));

        $product = $this->getProductRepository()->findOneByIdentifier('versioned-product');
        $this->assertTrue($product->isEnabled());
        $this->assertNull($product->getFamily());
        $this->assertCount(0, $product->getCategories());
        $this->assertCount(1, $product->getValues());
        $this->assertNotContains('groupB', $product->getGroups());
    }

    public function testRevertCreatesANewVersion()
    {
        $product = $this->getProductBuilder()->createProduct('versioned-product');
        $this->getProductSaver()->save($product);

        $updates = [
            'family' => 'familyA',
        ];
        $this->getProductUpdater()->update($product, $updates);
        $this->getProductSaver()->save($product);

        $productVersions = $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($product),
            $product->getId());
        $this->assertCount(2, $productVersions);

        $this->getProductReverter()->revert(end($productVersions));

        $productVersions = $this->getVersionRepository()->getLogEntries(ClassUtils::getClass($product),
            $product->getId());
        $this->assertCount(3, $productVersions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return VersionRepositoryInterface
     */
    protected function getVersionRepository()
    {
        return $this->get('pim_versioning.repository.version');
    }

    /**
     * @return ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->get('pim_catalog.repository.product');
    }

    /**
     * @return ObjectUpdaterInterface
     */
    protected function getProductUpdater()
    {
        return $this->get('pim_catalog.updater.product');
    }

    /**
     * @return SaverInterface
     */
    protected function getProductSaver()
    {
        return $this->get('pim_catalog.saver.product');
    }

    /**
     * @return ProductReverter
     */
    protected function getProductReverter()
    {
        return $this->get('pimee_versioning.reverter.product');
    }

    /**
     * @return ProductBuilderInterface
     */
    protected function getProductBuilder()
    {
        return $this->get('pim_catalog.builder.product');
    }
}
