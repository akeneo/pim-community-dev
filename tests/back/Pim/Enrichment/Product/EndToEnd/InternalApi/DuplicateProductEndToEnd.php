<?php

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\EndToEnd\InternalAPI;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class DuplicateProductEndToEnd extends InternalApiTestCase
{
    use AssertEventCountTrait;

    /** @var RouterInterface */
    private $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('duplicate_product');

        $this->router = $this->get('router');

        $adminUser = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $this->authenticate($adminUser);
    }

    public function test_it_duplicates_a_product_without_unique_values()
    {
        $familyCode = 'familyTest';
        $uniqueAttributeCodes = ['unique_attribute_1', 'unique_attribute_2'];
        $this->createAttributes($this->getAttributeData());
        $this->addAttributesToFamily($familyCode, $uniqueAttributeCodes);
        $associatedProduct = $this->createProduct(
            'associated_product',
            'familyA1',
            []
        );
        $normalizedProductToDuplicate = $this->getNormalizedProductToDuplicate($familyCode, $associatedProduct->getIdentifier());
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            $familyCode,
            [
                new SetGroups(['groupA', 'groupB']),
                new SetCategories(['categoryA', 'categoryB']),
                new SetIdentifierValue('sku', 'product_to_duplicate'),
                new SetTextValue('unique_attribute_1', null, null, 'The first unique value'),
                new SetTextValue('unique_attribute_2', null, null, 'The second unique value'),
                new SetTextValue('non_unique_attribute', null, null, 'value non unique'),
                new AssociateProducts('PACK', [$associatedProduct->getIdentifier()]),
                new AssociateGroups('UPSELL', ['groupA']),
                new AssociateProducts('X_SELL', [$associatedProduct->getIdentifier()]),
                new AssociateGroups('X_SELL', ['groupB']),
            ]
        );
        $this->clearMessageBusObserver();

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'uuid' => $productToDuplicate->getUuid()->toString(),
        ]);

        $this->client->request(
            'POST',
            $url,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(['duplicated_product_identifier' => 'duplicated_product'])
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEventCount(1, ProductCreated::class);

        $duplicatedProduct = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier('duplicated_product');
        Assert::assertNotNull($duplicatedProduct);

        $normalizedDuplicatedProductResponse = $this->get('pim_internal_api_serializer')->normalize(
            $duplicatedProduct,
            'internal_api'
        );
        Assert::assertEquals(
            [
                'unique_attribute_codes' => $uniqueAttributeCodes,
                'duplicated_product' => $normalizedDuplicatedProductResponse
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );

        $normalizedDuplicatedProduct = $this->get('pim_catalog.normalizer.standard.product')->normalize(
            $duplicatedProduct,
            'standard'
        );
        $this->assertSameData($normalizedDuplicatedProduct, $normalizedProductToDuplicate, $uniqueAttributeCodes);
    }

    /**
     * The whole product should be duplicated without any permission applied on:
     * - categories
     * - associations
     * - values
     *
     * This test only validate it on values.
     */
    public function test_it_duplicates_the_whole_product_without_any_permission_applied()
    {
        $this->modifyPermissionsForAttribute('a_metric', 'view');
        $this->modifyPermissionsForAttribute('a_number_float', 'neither_view_or_edit');
        $this->createUser('redactor_user');

        $redactorUser = $this->get('pim_user.repository.user')->findOneByIdentifier('redactor_user');
        $this->authenticate($redactorUser);

        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            'familyA2',
            [
                new SetMeasurementValue('a_metric', null, null, 1, 'WATT'),
                new SetNumberValue('a_number_float', null, null, '12.05')
            ]
        );

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'uuid' => $productToDuplicate->getUuid()->toString(),
        ]);

        $this->client->request(
            'POST',
            $url,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(['duplicated_product_identifier' => 'duplicated_product'])
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $duplicatedProduct = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier('duplicated_product');
        Assert::assertCount(3, $duplicatedProduct->getValues());
    }

    public function test_it_duplicates_a_product_without_family()
    {
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            null,
            []
        );

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'uuid' => $productToDuplicate->getUuid()->toString(),
        ]);

        $this->client->request(
            'POST',
            $url,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(['duplicated_product_identifier' => 'duplicated_product'])
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_validates_the_duplicated_product()
    {
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            'familyA',
            []
        );
        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'uuid' => $productToDuplicate->getUuid()->toString(),
        ]);

        $this->client->request(
            'POST',
            $url,
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            json_encode(['duplicated_product_identifier' => $productToDuplicate->getIdentifier()])
        );

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(
            [
              'values' => [
                  [
                    'attribute' => 'sku',
                    'locale' => null,
                    'scope' => null,
                    'message' => 'The product_to_duplicate identifier is already used for another product.',
                    'path' => 'identifier',
                  ]
              ]
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($data) {
            $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $errors = $this->get('validator')->validate($attribute);
            if (0 !== $errors->count()) {
                throw new \Exception(sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                ));
            }

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function addAttributesToFamily(string $familyCode, array $attributeCodes): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => $familyCode,
                'attributes'  =>  \array_unique(\array_merge(['sku'],$attributeCodes)),
            ]
        );

        $errors = $this->get('validator')->validate($family);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function assertSameData(
        array $normalizedDuplicatedProduct,
        array $normalizedProductToDuplicate,
        array $uniqueAttributeCodes
    ) {
        foreach($uniqueAttributeCodes as $attributeCode) {
            if (in_array($attributeCode, array_keys($normalizedProductToDuplicate['values']))) {
                unset($normalizedProductToDuplicate['values'][$attributeCode]);
            }
        }
        // Need to unset the 'sku' as its value is required and it couldn't be equal
        unset($normalizedProductToDuplicate['values']['sku']);
        unset($normalizedDuplicatedProduct['values']['sku']);

        Assert::assertEquals($normalizedProductToDuplicate['family'], $normalizedDuplicatedProduct['family']);
        Assert::assertEquals($normalizedProductToDuplicate['groups'], $normalizedDuplicatedProduct['groups']);
        Assert::assertEquals($normalizedProductToDuplicate['values'], $normalizedDuplicatedProduct['values']);
        Assert::assertEquals($normalizedProductToDuplicate['categories'], $normalizedDuplicatedProduct['categories']);
        Assert::assertEquals($normalizedProductToDuplicate['associations'], $normalizedDuplicatedProduct['associations']);
    }

    private function getNormalizedProductToDuplicate(string $familyCode, string $associatedProductIdentifier): array
    {
        return [
            'family' => $familyCode,
            'groups' => ['groupA', 'groupB'],
            'categories' => ['categoryA', 'categoryB'],
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'product_to_duplicate'
                    ]
                ],
                'unique_attribute_1' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'The first unique value'
                    ]
                ],
                'unique_attribute_2' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'The second unique value'
                    ]
                ],
                'non_unique_attribute' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'value non unique'
                    ]
                ]
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'product_models' => [],
                    'product_uuids' => [$this->getProductUuid($associatedProductIdentifier)->toString()]
                ],
                'UPSELL' => [
                    'groups' => ['groupA'],
                    'product_models' => [],
                    'product_uuids' => []
                ],
                'X_SELL' => [
                    'groups' => ['groupB'],
                    'product_models' => [],
                    'product_uuids' => [$this->getProductUuid($associatedProductIdentifier)->toString()]
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_models' => [],
                    'product_uuids' => []
                ]
            ]
        ];
    }

    private function getAttributeData()
    {
        return [
            [
                'code' => 'unique_attribute_1',
                'group' => 'other',
                'unique' => true,
            ],
            [
                'code' => 'unique_attribute_2',
                'group' => 'other',
                'unique' => true,
            ],
            [
                'code' => 'non_unique_attribute',
                'group' => 'other',
            ]
        ];
    }

    private function modifyPermissionsForAttribute(
        string $code,
        string $right
    ): void {
        switch ($right) {
            case 'view':
                $attributeGroup = 'attributeGroupB';
                break;
            case 'edit':
                $attributeGroup = 'attributeGroupA';
                break;
            default:
                $attributeGroup = 'attributeGroupC';
        }

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $this->get('pim_catalog.updater.attribute')->update($attribute, ['group' => $attributeGroup ]);
        $constraints = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createUser(string $username): User
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $groups = $this->get('pim_user.repository.group')->findAll();

        foreach ($groups as $group) {
            if ('Redactor' === $group->getName()) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
