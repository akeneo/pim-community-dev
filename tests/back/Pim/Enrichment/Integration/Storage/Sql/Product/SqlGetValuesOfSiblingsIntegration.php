<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
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
        $variantProduct = $this->createProduct('new_identifier', [
            new ChangeParent('sub_sweat_option_a'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);

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

    public function test_that_it_can_filter_values_by_attribute_codes()
    {
        $subSweatA = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat_option_a');

        $valuesOfSiblings = $this->getValuesOfSiblings($subSweatA, ['a_simple_select']);
        Assert::assertCount(1, $valuesOfSiblings);
        Assert::assertArrayHasKey('sub_sweat_option_b', $valuesOfSiblings);
        Assert::assertNull($valuesOfSiblings['sub_sweat_option_b']->getByCodes('a_text'));
        Assert::assertInstanceOf(
            ValueInterface::class,
            $valuesOfSiblings['sub_sweat_option_b']->getByCodes('a_simple_select')
        );
    }

    public function test_that_it_gets_the_siblings_values_of_an_existing_variant_product_without_identifier()
    {
        $apollonATrue = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optiona_true');
        $apollonAFalse = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optiona_false');
        $command = UpsertProductCommand::createWithUuid(
            userId: $this->getUserId('admin'),
            productUuid: ProductUuid::fromUuid($apollonATrue->getUuid()),
            userIntents: [
                new ClearValue('sku', null, null),
            ],
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        $valuesOfSiblings = $this->getValuesOfSiblings($apollonAFalse);
        Assert::assertCount(1, $valuesOfSiblings);
        Assert::assertArrayHasKey($apollonATrue->getUuid()->toString(), $valuesOfSiblings);
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
                new SetCategories(['master']),
                new ChangeParent('sub_sweat_option_a'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );
        $this->createProduct(
            'apollon_optiona_false',
            [
                new SetCategories(['master']),
                new ChangeParent('sub_sweat_option_a'),
                new SetBooleanValue('a_yes_no', null, null, false)
            ]
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct($identifier, array $userIntents = []): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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

    private function getValuesOfSiblings(EntityWithFamilyVariantInterface $entity, array $attributeCodes = []): array
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_values_of_siblings')
                    ->for($entity, $attributeCodes);
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }
}
