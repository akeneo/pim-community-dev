<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
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
        $productModel = $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
                'values' => []
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'family_variant' => 'familyVariantA1',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [
                        'data' => [
                            'data' => 'optionA',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
            ]
        );

        $product = $this->createProduct('p', [
            new SetFamily('familyA'),
            new ChangeParent('sub_pm_A'),
            new SetBooleanValue('a_yes_no', null, null, true),
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

        $productModel2 = $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
                'values' => []
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'family_variant' => 'familyVariantA1',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [
                        'data' => [
                            'data' => 'optionA',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
            ]
        );
        $product = $this->createProduct('p', [
            new SetFamily('familyA'),
            new ChangeParent('sub_pm_A'),
            new SetBooleanValue('a_yes_no', null, null, true),
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

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents = []): ProductInterface {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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
        $completenesses = $this->getProductCompletenesses()->fromProductUuid($product->getUuid());
        foreach ($completenesses as $completeness) {
            if ($localeCode === $completeness->localeCode()) {
                return $completeness;
            }
        }

        throw new \Exception(sprintf('No completeness for the locale "%s"', $localeCode));
    }
}
