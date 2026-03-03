<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Updater;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyClearer;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyClearerIntegration extends TestCase
{
    /** @var PropertyClearer */
    private $propertyClearer;

    /** @var ProductSaver */
    private $productSaver;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->propertyClearer = $this->get('pim_catalog.updater.property_clearer');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
    }

    public function test_it_clears_attribute_values(): void
    {
        $sku = 'test_localizable_title';
        $parameters = [
            new SetTextValue('a_text', null, null, 'the text'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'en_US', 'description'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'description'),
        ];
        $product = $this->createProduct($sku, $parameters);

        $this->propertyClearer->clear($product, 'a_text', []);
        $this->assertNull($product->getValue('a_text'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'tablet'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'fr_FR', 'tablet'));

        $this->propertyClearer->clear($product, 'a_localized_and_scopable_text_area', ['locale' => 'fr_FR', 'scope' => 'tablet']);
        $this->assertNull($product->getValue('a_text'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'tablet'));
        $this->assertNull($product->getValue('a_localized_and_scopable_text_area', 'fr_FR', 'tablet'));
    }

    public function test_it_clears_nothing_when_attribute_value_does_not_exist(): void
    {
        $sku = 'test_localizable_title';
        $parameters = [
            new SetTextValue('a_text', null, null, 'the text'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'description')
        ];
        $product = $this->createProduct($sku, $parameters);

        $this->propertyClearer->clear($product, 'a_localized_and_scopable_text_area', ['locale' => 'fr_FR', 'scope'  => 'ecommerce']);
        $this->assertNotNull($product->getValue('a_text'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce'));

        $this->propertyClearer->clear($product, 'a_metric');
        $this->assertNotNull($product->getValue('a_text'));
        $this->assertNotNull($product->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce'));
    }

    public function test_it_clears_associations(): void
    {
        $this->createProduct('a_product', []);
        $this->createProduct('another_product', []);

        $parameters = [
            new AssociateProducts('X_SELL', ['a_product', 'another_product'])
        ];
        $product = $this->createProduct('a_product_with_association', $parameters);

        $this->assertSame(2, $this->getAssociationsCount($product));

        $this->propertyClearer->clear($product, 'associations');
        $this->assertSame(0, $this->getAssociationsCount($product));

        // Save the product, clear the doctrine cache, reload the product and test the change is still good.
        $this->productSaver->save($product);
        $this->cacheClearer->clear();
        $product = $this->productRepository->findOneByIdentifier('a_product_with_association');
        $this->assertSame(0, $this->getAssociationsCount($product));
    }

    public function test_it_clears_categories(): void
    {
        $parameters = [
            new SetCategories(['categoryA', 'categoryB'])
        ];
        $product = $this->createProduct('a_product_with_categories', $parameters);
        $this->assertGreaterThan(0, $product->getCategories()->count());

        $this->propertyClearer->clear($product, 'categories');
        $this->assertCount(0, $product->getCategories());

        // Save the product, clear the doctrine cache, reload the product and test the change is still good.
        $this->productSaver->save($product);
        $this->cacheClearer->clear();
        $product = $this->productRepository->findOneByIdentifier('a_product_with_categories');
        $this->assertCount(0, $product->getCategories());
    }

    public function test_it_clears_groups(): void
    {
        $parameters = [
            new SetGroups(['groupA', 'groupB'])
        ];
        $product = $this->createProduct('a_product_with_groups', $parameters);
        $this->assertGreaterThan(0, $product->getGroups()->count());

        $this->propertyClearer->clear($product, 'groups');
        $this->assertCount(0, $product->getGroups());

        // Save the product, clear the doctrine cache, reload the product and test the change is still good.
        $this->productSaver->save($product);
        $this->cacheClearer->clear();
        $product = $this->productRepository->findOneByIdentifier('a_product_with_groups');
        $this->assertCount(0, $product->getGroups());
    }

    public function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function getAssociationsCount(EntityWithAssociationsInterface $entity): int
    {
        $count = 0;
        foreach ($entity->getAssociations() as $association) {
            $count += $association->getProducts()->count();
            $count += $association->getProductModels()->count();
            $count += $association->getGroups()->count();
        };

        return $count;
    }
}
