<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Manager;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

/**
 * Testing strategy:
 * - Create/update and delete product values or any other property from a product.
 * - Save the product
 * - Check that the version generated is compliant
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionManagerIntegration extends TestCase
{
    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @{@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->versionRepository = $this->get('pim_versioning.repository.version');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');
    }

    /**
     * @{@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function testNoVersionCreatedWhenThereIsNoUpdate()
    {
        $product = $this->productRepository->findOneByIdentifier('bar');
        $this->productSaver->save($product);
        $productVersions = $this->versionRepository->getLogEntries(ClassUtils::getClass($product), $product->getId());
        $this->assertEmpty($productVersions);
    }

    public function testCreateProductVersionOnProductCreation()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('versioned-product');
        $this->productSaver->save($product);

        $productVersions = $this->versionRepository->getLogEntries(ClassUtils::getClass($product), $product->getId());

        $this->assertCount(1, $productVersions);

        $version = current($productVersions);

        $this->assertEquals($version->getVersion(), 1);
        $this->assertEquals($version->getResourceName(), ClassUtils::getClass($product));
        $this->assertEquals($version->getResourceId(), $product->getId());

        $this->assertNotNull($version->getLoggedAt());
        $this->assertFalse($version->isPending());
        $this->assertNull($version->getContext());
        $this->assertEquals($version->getAuthor(), 'system');
        $this->assertEquals($version->getSnapshot(), [
            'sku'        => 'versioned-product',
            'family'     => '',
            'groups'     => '',
            'categories' => '',
            'parent'     => '',
            'enabled'    => 1,
        ]);
        $this->assertEquals($version->getChangeset(), [
            'sku'     => [
                'old' => '',
                'new' => 'versioned-product',
            ],
            'enabled' => [
                'old' => '',
                'new' => 1,
            ],
        ]);
    }

    public function testCreateProductVersionOnUpdate()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('versioned-product');
        $this->productSaver->save($product);

        $updates = [
            'groups' => ['groupB'],
            'values' => [
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2017-02-01'],
                ],
            ],
        ];

        $this->productUpdater->update($product, $updates);
        $this->productSaver->save($product);

        $this->assertEquals(
            $product->getRawValues()['a_date']['<all_channels>']['<all_locales>'],
            '2017-02-01T00:00:00+01:00'
        );

        $productVersions = $this->versionRepository->getLogEntries(ClassUtils::getClass($product), $product->getId());

        $this->assertCount(2, $productVersions);

        $version = current($productVersions);
        $this->assertEquals($version->getVersion(), 2);
        $this->assertEquals($version->getResourceName(), ClassUtils::getClass($product));
        $this->assertEquals($version->getResourceId(), $product->getId());
        $this->assertNotNull($version->getLoggedAt());
        $this->assertEquals($version->getSnapshot(), [
            'sku'        => 'versioned-product',
            'family'     => '',
            'groups'     => 'groupB',
            'categories' => '',
            'parent'     => '',
            'a_date'     => '2017-02-01T00:00:00+01:00',
            'enabled'    => 1,
        ]);

        $this->assertEquals($version->getChangeset(), [
            'groups' => [
                'old' => '',
                'new' => 'groupB',
            ],
            'a_date' => [
                'old' => '',
                'new' => '2017-02-01T00:00:00+01:00',
            ],
        ]);
    }

    public function testCreateProductVersionOnAttributeAndFieldDeletion()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('versioned-product');

        $updates = [
            'groups' => ['groupB'],
            'values' => [
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2017-02-01'],
                ],
            ],
        ];

        $this->productUpdater->update($product, $updates);
        $this->productSaver->save($product);

        $productValue = $product->getValue('a_date');
        $product->removeValue($productValue);
        $this->productSaver->save($product);

        $productVersions = $this->versionRepository->getLogEntries(ClassUtils::getClass($product), $product->getId());

        $this->assertCount(2, $productVersions);

        $version = current($productVersions);
        $this->assertEquals($version->getVersion(), 2);
        $this->assertEquals($version->getResourceName(), ClassUtils::getClass($product));
        $this->assertEquals($version->getResourceId(), $product->getId());
        $this->assertNotNull($version->getLoggedAt());
        $this->assertEquals($version->getSnapshot(), [
            'sku'        => 'versioned-product',
            'family'     => '',
            'groups'     => 'groupB',
            'categories' => '',
            'parent'     => '',
            'enabled'    => 1,
        ]);
        $this->assertEquals($version->getChangeset(), [
            'a_date' => [
                'old' => '2017-02-01T00:00:00+01:00',
                'new' => '',
            ],
        ]);
    }
}
