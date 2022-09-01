<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CreateTwoWayAssociationIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_creates_inversed_associations_when_owner_is_a_product()
    {
        $this->createProduct(
            'test',
            [
                new AssociateProducts('COMPATIBILITY', ['product_1']),
                new AssociateProductModels('COMPATIBILITY', ['product_model_1']),
                new AssociateGroups('COMPATIBILITY', ['groupA']),
            ]
        );

        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('test'),
            'COMPATIBILITY',
            ['product_1'],
            ['product_model_1'],
            ['groupA']
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1'),
            'COMPATIBILITY',
            ['test'],
            [],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('product_model_1'),
            'COMPATIBILITY',
            ['test'],
            [],
            []
        );
    }

    /**
     * @test
     */
    public function it_creates_inversed_associations_when_owner_is_a_product_model()
    {
        $this->createProductModel(
            [
                'code' => 'test_pm',
                'family_variant' => 'familyVariantA1',
                'associations' => [
                    'COMPATIBILITY' => [
                        'products' => ['product_1'],
                        'product_models' => ['product_model_1'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );

        $this->clearUnitOfWork();
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('test_pm'),
            'COMPATIBILITY',
            ['product_1'],
            ['product_model_1'],
            ['groupA']
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1'),
            'COMPATIBILITY',
            [],
            ['test_pm'],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('product_model_1'),
            'COMPATIBILITY',
            [],
            ['test_pm'],
            []
        );
    }

    /**
     * @test
     */
    public function it_removes_inversed_association_when_removing_associated_entities()
    {
        $this->createProduct(
            'test',
            [
                new AssociateProducts('COMPATIBILITY', ['product_1', 'product_2']),
                new AssociateProductModels('COMPATIBILITY', ['product_model_1', 'product_model_2']),
                new AssociateGroups('COMPATIBILITY', ['groupA']),
            ]
        );

        $this->createProduct('test', [
            new DissociateProducts('COMPATIBILITY', ['product_1']),
            new DissociateProductModels('COMPATIBILITY', ['product_model_1']),
            new DissociateGroups('COMPATIBILITY', ['groupA'])
        ]);

        $productModel = $this->createProductModel(
            [
                'code' => 'test_pm',
                'family_variant' => 'familyVariantA1',
                'associations' => [
                    'COMPATIBILITY' => [
                        'products' => ['product_1', 'product_2'],
                        'product_models' => ['product_model_1', 'product_model_2'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'associations' => [
                    'COMPATIBILITY' => [
                        'products' => ['product_1'],
                        'product_models' => ['product_model_1'],
                        'groups' => [],
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->clearUnitOfWork();
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('test'),
            'COMPATIBILITY',
            ['product_2'],
            ['product_model_2'],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('test_pm'),
            'COMPATIBILITY',
            ['product_1'],
            ['product_model_1'],
            []
        );

        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1'),
            'COMPATIBILITY',
            [],
            ['test_pm'],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_2'),
            'COMPATIBILITY',
            ['test'],
            [],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('product_model_1'),
            'COMPATIBILITY',
            [],
            ['test_pm'],
            []
        );
        $this->assertAssociations(
            $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('product_model_2'),
            'COMPATIBILITY',
            ['test'],
            [],
            []
        );
    }

    /**
     * @test
     */
    public function it_deletes_inversed_associations_when_the_owner_is_deleted()
    {
        $product = $this->createProduct(
            'test',
            [new AssociateProducts('COMPATIBILITY', ['product_1'])]
        );
        $productModel = $this->createProductModel(
            [
                'code' => 'test_pm',
                'family_variant' => 'familyVariantA1',
                'associations' => [
                    'COMPATIBILITY' => [
                        'products' => ['product_1'],
                    ],
                ],
            ]
        );

        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1'),
            'COMPATIBILITY',
            ['test'],
            ['test_pm'],
            []
        );

        $this->get('pim_catalog.remover.product')->remove($product);
        $this->get('pim_catalog.remover.product_model')->remove($productModel);
        $this->clearUnitOfWork();

        $this->assertAssociations(
            $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_1'),
            'COMPATIBILITY',
            [],
            [],
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $associationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $associationType,
            [
                'code' => 'COMPATIBILITY',
                'is_two_way' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($associationType);

        $this->createProduct('product_1', []);
        $this->createProduct('product_2', []);
        $this->createProductModel(
            [
                'code' => 'product_model_1',
                'family_variant' => 'familyVariantA1',
            ]
        );
        $this->createProductModel(
            [
                'code' => 'product_model_2',
                'family_variant' => 'familyVariantA1',
            ]
        );
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

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearUnitOfWork();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is invalid: %s', (string)$violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function assertAssociations(
        EntityWithAssociationsInterface $entity,
        string $associationTypeCode,
        array $expectedAssociatedProductIdentifiers,
        array $expectedAssociatedProductModelCodes,
        array $expectedAssociatedGroupCodes
    ): void {
        $associatedProducts = $entity->getAssociatedProducts($associationTypeCode);
        if (empty($expectedAssociatedProductIdentifiers)) {
            Assert::assertTrue(
                null === $associatedProducts || $associatedProducts->isEmpty(),
                'Expected associated products to be empty, but they\'re not'
            );
        } else {
            Assert::assertNotNull($associatedProducts);
            Assert::assertEquals(
                $expectedAssociatedProductIdentifiers,
                $associatedProducts->map(
                    fn (ProductInterface $product): string => $product->getIdentifier()
                )->toArray()
            );
        }

        $associatedProductModels = $entity->getAssociatedProductModels($associationTypeCode);
        if (empty($expectedAssociatedProductModelCodes)) {
            Assert::assertTrue(
                null === $associatedProductModels || $associatedProductModels->isEmpty(),
                'Expected associated product models to be empty, but they\'re not'
            );
        } else {
            Assert::assertNotNull($associatedProductModels);
            Assert::assertEquals(
                $expectedAssociatedProductModelCodes,
                $associatedProductModels->map(
                    fn (ProductModelInterface $productModel): string => $productModel->getCode()
                )->toArray()
            );
        }

        $associatedGroups = $entity->getAssociatedGroups($associationTypeCode);
        if (empty($expectedAssociatedGroupCodes)) {
            Assert::assertTrue(
                null === $associatedGroups || $associatedGroups->isEmpty(),
                'Expected associated groups to be empty, but they\'re not'
            );
        } else {
            Assert::assertNotNull($associatedGroups);
            Assert::assertEquals(
                $expectedAssociatedGroupCodes,
                $associatedGroups->map(
                    fn (GroupInterface $group): string => $group->getCode()
                )->toArray()
            );
        }
    }

    private function clearUnitOfWork(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
