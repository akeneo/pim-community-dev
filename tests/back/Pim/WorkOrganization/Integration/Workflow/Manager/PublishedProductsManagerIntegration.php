<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

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
    use AssertEventCountTrait;

    private PublishedProductManager $publishedProductManager;
    private PublishedProductRepositoryInterface $publishedProductRepository;
    private ProductRepositoryInterface $productRepository;
    private SaverInterface $productSaver;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $productBuilder = $this->get('akeneo_integration_tests.catalog.product.builder');

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
        $this->get('pim_catalog.saver.product')->saveAll([$foo, $bar]);

        $this->publishedProductRepository = $this->get('pimee_workflow.repository.published_product');
        $this->publishedProductManager = $this->get('pimee_workflow.manager.published_product');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->clearMessageBusObserver();
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

        $this->assertEventCount(0, ProductCreated::class);
        $this->assertEventCount(0, ProductUpdated::class);
        $publishedProduct = $this->publishedProductRepository->findOneByOriginalProduct($product);

        $this->assertPublishedProductPropertiesEqual($product, $publishedProduct);
        $this->assertEqualsCanonicalizing($product->getRawValues(), $publishedProduct->getRawValues());
        $this->assertValuesEqual($product->getValues()->toArray(), $publishedProduct->getValues()->toArray());
        $this->assertProductAssociationsEqual($product, $publishedProduct);

        $productCompletenesses = $this->getProductCompletenesses()->fromProductId($product->getId());
        $publishedProductCompletenesses = $this->getPublishedProductCompletenesses()->fromPublishedProductId($publishedProduct->getId());

        $this->assertSameCompletenesses($productCompletenesses, $publishedProductCompletenesses);
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

        $this->assertEventCount(0, ProductCreated::class);
        $this->assertEventCount(0, ProductUpdated::class);

        $this->assertNotNull($this->publishedProductRepository->findOneByOriginalProduct($productBar));
        $this->assertNotNull($this->publishedProductRepository->findOneByOriginalProduct($productFoo));
    }

    public function testUnpublishAProduct()
    {
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->publishedProductManager->publish($product);

        $this->assertEventCount(0, ProductUpdated::class);
        $this->assertEventCount(0, ProductRemoved::class);

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

    private function assertSameCompletenesses(
        ProductCompletenessCollection $completenesses,
        PublishedProductCompletenessCollection $publishedCompletenesses
    ) {
        foreach ($completenesses as $completeness) {
            foreach ($publishedCompletenesses as $publishedCompleteness) {
                if ($completeness->localeCode() === $publishedCompleteness->localeCode() &&
                    $completeness->channelCode() === $publishedCompleteness->channelCode()
                ) {
                    $this->assertSame($completeness->ratio(), $publishedCompleteness->ratio());
                    $this->assertSame($completeness->requiredCount(), $publishedCompleteness->requiredCount());
                    $this->assertSame($completeness->missingCount(), $publishedCompleteness->missingCount());
                }
            }
        }
    }

    private function getProductCompletenesses(): GetProductCompletenesses
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses');
    }

    private function getPublishedProductCompletenesses(): GetPublishedProductCompletenesses
    {
        return $this->get('pimee_workflow.query.get_published_product_completenesses');
    }
}
