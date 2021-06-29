<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Updater;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyClearer;
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
            'values' => [
                'a_text' => [
                    ['data' => 'the text', 'locale' => null, 'scope'  => null],
                ],
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'description', 'locale' => 'en_US', 'scope'  => 'ecommerce'],
                    ['data' => 'description', 'locale' => 'en_US', 'scope'  => 'tablet'],
                    ['data' => 'description', 'locale' => 'fr_FR', 'scope'  => 'tablet'],
                ],
            ],
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
            'values' => [
                'a_text' => [
                    ['data' => 'the text', 'locale' => null, 'scope'  => null],
                ],
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'description', 'locale' => 'en_US', 'scope'  => 'ecommerce'],
                ],
            ],
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
            'associations' => [
                'X_SELL' => [
                    'products' => ['a_product', 'another_product'],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
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
            'categories' => ['categoryA', 'categoryB'],
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
            'groups' => ['groupA', 'groupB'],
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

    protected function createProduct(string $sku, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($sku);
        $this->get('pim_catalog.updater.product')->update($product, $data);

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
