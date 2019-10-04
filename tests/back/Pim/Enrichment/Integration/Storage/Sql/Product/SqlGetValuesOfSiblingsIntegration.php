<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetValuesOfSiblingsIntegration extends TestCase
{
    public function test_that_it_gets_the_siblings_values_of_a_new_product_model()
    {
        $productModel = $this->createProductModel(
            [
                'code' => 'new_sub_pm',
                'parent' => 'sweat',
            ],
            false
        );

        $valuesOfSiblings = $this->getValuesOfSiblings($productModel);
        Assert::assertCount(2, $valuesOfSiblings);
        Assert::assertArrayHasKey('sub_sweat_option_a', $valuesOfSiblings);
        Assert::assertArrayHasKey('sub_sweat_option_b', $valuesOfSiblings);
    }

    public function test_that_it_gets_the_siblings_values_of_an_existing_product_model()
    {
        $subSweatA = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat_option_a');

        $valuesOfSiblings = $this->getValuesOfSiblings($subSweatA);
        Assert::assertCount(1, $valuesOfSiblings);
        Assert::assertArrayHasKey('sub_sweat_option_b', $valuesOfSiblings);
        Assert::assertNull($valuesOfSiblings['sub_sweat_option_b']->getByCodes('a_number_integer'));
        Assert::assertInstanceOf(ValueInterface::class, $valuesOfSiblings['sub_sweat_option_b']->getByCodes('a_text'));
    }

    public function test_that_it_gets_the_siblings_values_of_a_new_variant_product()
    {
        $variantProduct = $this->createProduct(
            'new_identifier',
            [
                'parent' => 'sub_sweat_option_a',
            ],
            false
        );

        $valuesOfSiblings = $this->getValuesOfSiblings($variantProduct);
        Assert::assertCount(2, $valuesOfSiblings);
        Assert::assertArrayHasKey('apollon_optiona_true', $valuesOfSiblings);
        Assert::assertArrayHasKey('apollon_optiona_false', $valuesOfSiblings);
    }

    public function test_that_it_gets_the_siblings_values_of_an_existing_variant_product()
    {
        $apollonATrue = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optiona_true');

        $valuesOfSiblings = $this->getValuesOfSiblings($apollonATrue);
        Assert::assertCount(1, $valuesOfSiblings);
        Assert::assertArrayHasKey('apollon_optiona_false', $valuesOfSiblings);
    }

    // - sweat
    //     - sub_sweat_option_a
    //         - apollon_optiona_true
    //         - apollon_optiona_false
    //     - sub_sweat_option_b
    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel(
            [
                'code' => 'sweat',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_number_integer' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 42,
                        ],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_sweat_option_a',
                'parent' => 'sweat',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_sweat_option_b',
                'parent' => 'sweat',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionB',
                        ],
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'Lorem ipsum',
                        ],
                    ],
                ],
            ]
        );
        $this->createProduct(
            'apollon_optiona_true',
            [
                'categories' => ['master'],
                'parent' => 'sub_sweat_option_a',
                'values' => [
                    'a_yes_no' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
            ]
        );
        $this->createProduct(
            'apollon_optiona_false',
            [
                'categories' => ['master'],
                'parent' => 'sub_sweat_option_a',
                'values' => [
                    'a_yes_no' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => false,
                        ],
                    ],
                ],
            ]
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct($identifier, array $data = [], bool $save = true): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        if (true === $save) {
            $errors = $this->get('pim_catalog.validator.product')->validate($product);
            if (0 !== $errors->count()) {
                throw new \Exception(
                    sprintf(
                        'Impossible to setup test in %s: %s',
                        static::class,
                        $errors->get(0)->getMessage()
                    )
                );
            }

            $this->get('pim_catalog.saver.product')->save($product);
        }

        return $product;
    }

    private function createProductModel(array $data = [], bool $save = true): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        if (true === $save) {
            $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
            if (0 !== $errors->count()) {
                throw new \Exception(
                    sprintf(
                        'Impossible to setup test in %s: %s',
                        static::class,
                        $errors->get(0)->getMessage()
                    )
                );
            }
            $this->get('pim_catalog.saver.product_model')->save($productModel);
        }

        return $productModel;
    }

    private function getValuesOfSiblings(EntityWithFamilyVariantInterface $entity): array
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_values_of_siblings')->for($entity);
    }
}
