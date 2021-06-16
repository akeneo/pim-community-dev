<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\EntityWithValue;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelRepositoryIntegration extends TestCase
{
    public function testCanFindChildrenProductsFilteredByPermissions()
    {
        $productModel = $this->createProductModel();
        $this->createVariantProducts($productModel);

        $this->logAs('mary');

        $products = $this->getProductModelRepositoryInterface()->findChildrenProducts($productModel);
        $this->assertCount(1, $products);
    }

    public function testCanFindFirstCreatedVariantProductModelFilteredByPermissions()
    {
        $productModel = $this->createProductModel();
        $this->createVariantProductModels($productModel);
        $childrenProductModel = $this->getProductModelRepositoryInterface()->findFirstCreatedVariantProductModel(
            $productModel
        );

        $this->assertInstanceOf(ProductModelInterface::class, $childrenProductModel);
        $this->assertEquals('a_variant_product_model', $childrenProductModel->getCode());
    }

    public function testItReturnsNullWhenUserHaveNoViewPermissionOnFirstCreatedChildrenProductModel()
    {
        $productModel = $this->createProductModel();
        $this->createVariantProductModels($productModel);
        $this->logAs('mary');
        $childrenProductModel = $this->getProductModelRepositoryInterface()->findFirstCreatedVariantProductModel(
            $productModel
        );

        $this->assertNull($childrenProductModel);
    }

    private function getProductModelRepositoryInterface(): ProductModelRepositoryInterface
    {
        return $this->get('pimee_security.repository.product_model');
    }

    private function logAs(string $username): void
    {
        $user = $this->getUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', ['ROLE_USER']);
        $this->get('security.token_storage')->setToken($token);
    }

    private function getUserByUsername(string $username): UserInterface
    {
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $this->get('pim_user.repository.user');

        return $userRepository->findOneBy(['username' => $username]);
    }
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(): ProductModelInterface
    {
        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProductModel('a_product_model', 'familyVariantA2', null, []);
    }

    private function createVariantProductModels(ProductModelInterface $productModel): void
    {
        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createProductModel(
            'a_variant_product_model',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryB'],
            ]
        );

        $entityBuilder->createProductModel(
            'another_variant_product_model',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryA1'],
            ]
        );
    }

    private function createVariantProducts(ProductModelInterface $productModel): void
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createVariantProduct(
            'a_variant_product',
            'familyA',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryA1'],
            ]
        );

        $entityBuilder->createVariantProduct(
            'b_variant_product',
            'familyA',
            'familyVariantA2',
            $productModel,
            [
                'values' => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                    'a_yes_no' =>  [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => true,
                        ],
                    ],
                ],
                'categories' => ['categoryB'],
            ]
        );
    }
}
