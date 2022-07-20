<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDescendantVariantProductUuidsIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function test_that_it_gets_descendant_identifiers_of_sub_product_models()
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => 'optionC',
            'attribute' => 'a_simple_select',
            'labels' => ['en_US' => 'C option'],
        ]);
        $violations = $this->get('validator')->validate($attributeOption);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        $this->createFamilyVariant(
            [
                'code' => 'shirt_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size']);
        $aSmallShirt = $this->createProduct('a_small_shirt', 'familyA', 'a_shirt', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')]);
        $aMediumShirt = $this->createProduct('a_medium_shirt', 'familyA', 'a_shirt', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionB')]);
        $aLargeShirt = $this->createProduct('a_large_shirt', 'familyA', 'a_shirt', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionC')]);

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size']);
        $aSmallShoe = $this->createProduct('a_small_shoe', 'familyA', 'a_shoe', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')]);
        $aMediumShoe = $this->createProduct('a_medium_shoe', 'familyA', 'a_shoe', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionB')]);
        $aLargeShoe = $this->createProduct('a_large_shoe', 'familyA', 'a_shoe', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionC')]);

        Assert::assertEqualsCanonicalizing(
            [$aSmallShirt->getUuid(), $aMediumShirt->getUuid(), $aLargeShirt->getUuid(), $aSmallShoe->getUuid(), $aMediumShoe->getUuid(), $aLargeShoe->getUuid()],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_uuids')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );
    }

    public function test_that_it_gets_descendant_identifiers_of_root_product_models()
    {
        $this->createFamilyVariant(
            [
                'code' => 'shirt_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_simple_select'], 'level' => 2],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size_color']);
        $this->createProductModel(
            [
                'code' => 'a_medium_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'a_large_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );
        $aMediumRedShirt = $this->createProduct('a_medium_red_shirt', 'familyA', 'a_medium_shirt');
        $aMediumBlueShirt = $this->createProduct('a_medium_blue_shirt', 'familyA', 'a_medium_shirt');
        $aLargeBlackShirt = $this->createProduct('a_large_black_shirt', 'familyA', 'a_large_shirt');

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_simple_select'], 'level' => 2],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size_color']);
        $this->createProductModel(
            [
                'code' => 'a_large_shoe',
                'family_variant' => 'shoe_size_color',
                'parent' => 'a_shoe',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );
        $aLargeRedShoe = $this->createProduct('a_large_red_shoe', 'familyA', 'a_large_shoe');
        $aLargeGreenShoe = $this->createProduct('a_large_green_shoe', 'familyA', 'a_large_shoe');

        Assert::assertEqualsCanonicalizing(
            [$aMediumRedShirt->getUuid(), $aMediumBlueShirt->getUuid(), $aLargeBlackShirt->getUuid(), $aLargeRedShoe->getUuid(), $aLargeGreenShoe->getUuid()],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_uuids')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );
    }

    public function test_that_it_gets_descendant_identifiers_of_both_root_and_sub_product_models()
    {
        $this->createFamilyVariant(
            [
                'code' => 'shirt_size_color',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                    ['axes' => ['a_simple_select'], 'level' => 2],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size_color']);
        $this->createProductModel(
            [
                'code' => 'a_medium_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'a_large_shirt',
                'family_variant' => 'shirt_size_color',
                'parent' => 'a_shirt',
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );
        $aMediumRedShirt = $this->createProduct('a_medium_red_shirt', 'familyA', 'a_medium_shirt');
        $aMediumBlueShirt = $this->createProduct('a_medium_blue_shirt', 'familyA', 'a_medium_shirt');
        $aLargeBlackShirt = $this->createProduct('a_large_black_shirt', 'familyA', 'a_large_shirt');

        $this->createFamilyVariant(
            [
                'code' => 'shoe_size',
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    ['axes' => ['a_simple_select'], 'level' => 1],
                ],
            ]
        );
        $this->createProductModel(['code' => 'a_shoe', 'family_variant' => 'shoe_size']);
        $aLargeShoe = $this->createProduct('a_large_shoe', 'familyA', 'a_shoe', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionA')]);
        $aMediumShoe = $this->createProduct('a_medium_shoe', 'familyA', 'a_shoe', [new SetSimpleSelectValue('a_simple_select', null, null, 'optionB')]);

        Assert::assertEqualsCanonicalizing(
            [$aMediumRedShirt->getUuid(), $aMediumBlueShirt->getUuid(), $aLargeBlackShirt->getUuid(), $aLargeShoe->getUuid(), $aMediumShoe->getUuid()],
            $this->get('akeneo.pim.enrichment.product.query.get_descendant_variant_product_uuids')
                ->fromProductModelCodes(['a_shirt', 'a_shoe'])
        );
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);

        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }

    private function createProductModel(array $data = []): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

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

        return $productModel;
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, string $familyCode, string $parentCode, array $userIntents = []): ProductInterface {
        $this->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: \array_merge(
                [
                    new SetFamily($familyCode),
                    new ChangeParent($parentCode)
                ],
                $userIntents
            )
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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

    private function logIn(string $username): void
    {
        $session = $this->get('session');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $session->set('_security_main', serialize($token));
        $session->save();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }
}
