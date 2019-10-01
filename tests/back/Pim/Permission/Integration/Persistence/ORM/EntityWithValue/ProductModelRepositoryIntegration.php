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
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
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
        /** @var UserRepository $userRepository */
        $userRepository = $this->get('pim_user.repository.user');
        /** @var User $user */
        $user = $userRepository->findOneBy(['username' => 'mary']);
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', ['ROLE_USER']);
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->get('security.token_storage');
        $tokenStorage->setToken($token);
        $productModel = $this->createProductModelVariantProducts();

        $products = $this->get('pimee_security.repository.product_model')->findChildrenProducts($productModel);
        $this->assertCount(1, $products);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModelVariantProducts(): ProductModelInterface
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $productModel = $entityBuilder->createProductModel('a_product_model', 'familyVariantA2', null, []);

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

        return $productModel;
    }
}
