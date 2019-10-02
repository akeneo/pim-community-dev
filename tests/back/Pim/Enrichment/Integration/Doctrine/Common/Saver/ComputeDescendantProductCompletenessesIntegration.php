<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use AkeneoTest\Pim\Enrichment\Integration\Completeness\AbstractCompletenessTestCase;
use PHPUnit\Framework\Assert;

/**
 * Test the completenesses of variant products are updated after product model save.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeDescendantProductCompletenessesIntegration extends AbstractCompletenessTestCase
{
    public function test_completeness_is_updated_after_product_model_save()
    {
        $productModel = $this->createProductModel([
            'code' => 'pm',
            'family_variant' => 'familyVariantA1',
        ]);
        $product = $this->createProduct('p', $productModel, [
            'a_text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'pouet',
                ],
            ],
        ]);
        $completenessBeforeSave = $this->getCompletenessByLocaleCode($product, 'en_US');

        $data = [
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_date' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '2019-05-19',
                    ],
                ],
            ],
        ];
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $completenessAfterSave = $this->getCompletenessByLocaleCode($product, 'en_US');

        $this->assertNotEquals($completenessBeforeSave->ratio(), $completenessAfterSave->ratio());
    }

    public function test_completeness_is_updated_after_bulk_product_models_save()
    {
        $productModel1 = $this->createProductModel([
            'code' => 'pm1',
            'family_variant' => 'familyVariantA1',
        ]);
        $productModel2 = $this->createProductModel([
            'code' => 'pm2',
            'family_variant' => 'familyVariantA1',
        ]);
        $product = $this->createProduct('p', $productModel1, [
            'a_text' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'pouet',
                ],
            ],
        ]);
        $completenessBeforeSave = $this->getCompletenessByLocaleCode($product, 'en_US');

        $data = [
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_date' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '2019-05-19',
                    ],
                ],
            ],
        ];
        $this->get('pim_catalog.updater.product_model')->update($productModel1, $data);
        $this->get('pim_catalog.updater.product_model')->update($productModel2, $data);
        $this->get('pim_catalog.saver.product_model')->saveAll([$productModel1, $productModel2]);
        $completenessAfterSave = $this->getCompletenessByLocaleCode($product, 'en_US');

        $this->assertNotEquals($completenessBeforeSave->ratio(), $completenessAfterSave->ratio());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createProduct(
        string $identifier,
        ?ProductModelInterface $productModel,
        array $values = []
    ): ProductInterface {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => $productModel->getCode(),
            'values' => $values
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param string           $localeCode
     *
     * @return ProductCompleteness
     * @throws \Exception
     */
    private function getCompletenessByLocaleCode(ProductInterface $product, $localeCode)
    {
        $completenesses = $this->getProductCompletenesses()->fromProductId($product->getId());
        foreach ($completenesses as $completeness) {
            if ($localeCode === $completeness->localeCode()) {
                return $completeness;
            }
        }

        throw new \Exception(sprintf('No completeness for the locale "%s"', $localeCode));
    }
}
