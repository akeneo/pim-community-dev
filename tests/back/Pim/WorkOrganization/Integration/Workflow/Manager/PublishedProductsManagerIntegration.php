<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Manager;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Collections\Collection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;

/**
 * Testing strategy:
 * - publish an unpublished product
 * - Test that product update does not impact its published version
 * - Unpublish a product
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishedProductsManagerIntegration extends TestCase
{
    /** @var PublishedProductManager */
    private $publishedProductManager;

    /** @var PublishedProductRepositoryInterface */
    private $publishedProductRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SaverInterface */
    private $productSaver;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $productBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder');

        $foo = $productBuilder
            ->withIdentifier('foo')
            ->withValue(
                'a_scopable_price',
                [
                    ['amount' => '10.50', 'currency' => 'EUR'],
                    ['amount' => '11.50', 'currency' => 'USD'],
                ],
                '',
                'ecommerce'
            )->build();

        $bar = $productBuilder
            ->withIdentifier('bar')
            ->withValue(
                'a_scopable_price',
                [
                    ['amount' => '10.50', 'currency' => 'EUR'],
                    ['amount' => '11.50', 'currency' => 'USD'],
                ],
                '',
                'ecommerce'
            )->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->saveAll([$foo, $bar]);

        $this->publishedProductRepository = $this->get('pimee_workflow.repository.published_product');
        $this->publishedProductManager = $this->get('pimee_workflow.manager.published_product');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testPublishProduct()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->publishedProductManager->publish($product);

        $publishedProduct = $this->publishedProductRepository->findOneByOriginalProduct($product);

        $this->assertPublishedProductPropertiesEqual($product, $publishedProduct);
        $this->assertEquals($product->getRawValues(), $publishedProduct->getRawValues(), '', 0.0, 10, true);
        $this->assertValuesEqual($product->getValues()->toArray(), $publishedProduct->getValues()->toArray());
        $this->assertProductAssociationsEqual($product, $publishedProduct);


        $this->assertSameCompletenesses($product->getCompletenesses(), $publishedProduct->getCompletenesses());
    }

    public function testProductUpdateDoesNotImpactPublishedProduct()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->publishedProductManager->publish($product);

        $product = $this->productRepository->findOneByIdentifier('foo');
        $productValue = $product->getValue('a_scopable_price', null, 'ecommerce');
        $product->removeValue($productValue);
        $this->productSaver->save($product);

        $publishedProduct = $this->publishedProductRepository->findOneByOriginalProduct($product);
        $this->assertNotNull($publishedProduct->getValue('a_scopable_price', null, 'ecommerce'));
    }

    public function testPublishAnAlreadyPublishedProduct()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->publishedProductManager->publish($product);

        $product = $this->productRepository->findOneByIdentifier('foo');
        $productValue = $product->getValue('a_scopable_price', null, 'ecommerce');
        $product->removeValue($productValue);
        $this->productSaver->save($product);
        $this->publishedProductManager->publish($product);

        $publishedProduct = $this->publishedProductRepository->findOneByOriginalProduct($product);
        $this->assertNull($publishedProduct->getValue('a_scopable_price', null, 'ecommerce'));
        $this->assertPublishedProductPropertiesEqual($product, $publishedProduct);
        $this->assertProductAssociationsEqual($product, $publishedProduct);
    }

    public function testPublishMultipleProductsAtOnce()
    {
        $productFoo = $this->productRepository->findOneByIdentifier('foo');
        $productBar = $this->productRepository->findOneByIdentifier('bar');
        $this->publishedProductManager->publishAll([$productFoo, $productBar]);

        $this->assertNotNull($this->publishedProductRepository->findOneByOriginalProduct($productBar));
        $this->assertNotNull($this->publishedProductRepository->findOneByOriginalProduct($productFoo));
    }

    public function testUnpublishAProduct()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->publishedProductManager->publish($product);

        $publishedProduct = $this->publishedProductRepository->findOneByOriginalProduct($product);
        $this->assertNotNull($publishedProduct);

        $this->publishedProductManager->unpublish($publishedProduct);
        $this->assertNull($this->publishedProductRepository->findOneByOriginalProduct($product));
    }

    /**
     * Asserts the basic properties of a published product are the same as the product.
     *
     * @param PublishedProductInterface $publishedProduct
     * @param ProductInterface          $product
     */
    protected function assertPublishedProductPropertiesEqual($product, $publishedProduct)
    {
        $this->assertNotNull($publishedProduct);
        $this->assertNotNull($publishedProduct->getOriginalProduct());

        $this->assertEquals($product->getId(), $publishedProduct->getOriginalProduct()->getId());
        $this->assertEquals($product->getIdentifier(), $publishedProduct->getIdentifier());
        $this->assertEquals($product->isEnabled(), $publishedProduct->isEnabled());
        $this->assertEquals($product->getFamilyId(), $publishedProduct->getFamilyId());
        $this->assertNotNull($publishedProduct->getVersion()->getId());
        $this->assertEquals($publishedProduct->getGroups()->toArray(), $publishedProduct->getGroups()->toArray());
        $this->assertEquals(
            $publishedProduct->getCategories()->toArray(),
            $publishedProduct->getCategories()->toArray()
        );
    }

    /**
     * Asserts the product values of a published product are the same as the product.
     *
     * @param array $publishedProductValues
     * @param array $productValues
     */
    protected function assertValuesEqual($productValues, $publishedProductValues)
    {
        $this->assertEquals(count($productValues), count($publishedProductValues));
        foreach ($productValues as $i => $originalProductValue) {
            $this->assertEquals($originalProductValue, $publishedProductValues[$i]);
        }
    }

    /**
     * Asserts the product associations of a published product are the same as the product.
     *
     * @param PublishedProductInterface $publishedProduct
     * @param ProductInterface          $product
     *
     */
    protected function assertProductAssociationsEqual($product, $publishedProduct)
    {
        $this->assertEquals($product->getAssociations()->count(), $publishedProduct->getAssociations()->count());
        $sortingFunction = function ($associationA, $associationB) {
            return strcmp($associationA->getReference(), $associationB->getReference());
        };
        $publishedProductAssociations = $publishedProduct->getAssociations()->toArray();
        $productAssociations = $publishedProduct->getAssociations()->toArray();
        usort($publishedProductAssociations, $sortingFunction);
        usort($productAssociations, $sortingFunction);

        foreach ($productAssociations as $i => $originalAssociation) {
            $this->assertEquals(
                $originalAssociation->getReference(),
                $publishedProductAssociations[$i]->getReference()
            );
        }
    }

    /**
     * @param Collection $completenesses
     * @param Collection $publishedCompletenesses
     */
    protected function assertSameCompletenesses(Collection $completenesses, Collection $publishedCompletenesses)
    {
        foreach ($completenesses as $completeness) {
            foreach ($publishedCompletenesses as $publishedCompleteness) {
                if ($completeness->getLocale() === $publishedCompleteness->getLocale() &&
                    $completeness->getChannel() === $publishedCompleteness->getChannel()
                ) {
                    $this->assertSame($completeness->getRatio(), $publishedCompleteness->getRatio());
                    $this->assertSame($completeness->getRequiredCount(), $publishedCompleteness->getRequiredCount());
                    $this->assertSame($completeness->getMissingCount(), $publishedCompleteness->getMissingCount());
                }
            }
        }
    }
}

