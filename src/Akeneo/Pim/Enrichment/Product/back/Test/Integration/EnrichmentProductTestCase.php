<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class EnrichmentProductTestCase extends TestCase
{
    protected MessageBusInterface $messageBus;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBus = $this->get('pim_enrich.product.message_bus');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function loadEnrichmentProductFunctionalFixtures(): void
    {
        $this->createUser('mary', ['ROLE_USER'], ['Redactor']);
        $this->createUser('betty', ['ROLE_USER'], ['Manager']);
        $this->createUser('peter', ['ROLE_USER'], ['IT support']);

        $this->createCategory(['code' => 'print']);
        $this->createCategory(['code' => 'suppliers']);
        $this->createCategory(['code' => 'sales']);

        if (FeatureHelper::isPermissionFeatureActivated()) {
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver')->save('All', [
                'own' => ['all' => false, 'identifiers' => []],
                'edit' => ['all' => false, 'identifiers' => []],
                'view' => ['all' => false, 'identifiers' => []],
            ]);
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver')->save('Redactor', [
                'own' => ['all' => false, 'identifiers' => []],
                'edit' => ['all' => false, 'identifiers' => ['print', 'suppliers', 'sales']],
                'view' => ['all' => false, 'identifiers' => ['print', 'suppliers', 'sales']],
            ]);
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver')->save('Manager', [
                'own' => ['all' => false, 'identifiers' => ['print']],
                'edit' => ['all' => false, 'identifiers' => ['print']],
                'view' => ['all' => false, 'identifiers' => ['print', 'sales']],
            ]);
            $this->get('Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver')->save('IT Support', [
                'own' => ['all' => false, 'identifiers' => ['print', 'suppliers', 'sales']],
                'edit' => ['all' => false, 'identifiers' => ['print', 'suppliers', 'sales']],
                'view' => ['all' => false, 'identifiers' => ['print', 'suppliers', 'sales']],
            ]);
        }

        $this->createAttribute('name', ['type' => AttributeTypes::TEXT]);
        $this->createAttribute('sub_name', ['type' => AttributeTypes::TEXT]);
        $this->createAttribute('main_color', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('main_color', ['red', 'blue', 'green', 'white']);

        $this->createFamily('accessories', ['attributes' => ['name', 'sub_name', 'main_color']]);
        $this->createFamilyVariant('color_variant_accessories', 'accessories', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['main_color'],
                    'attributes' => [],
                ],
            ],
        ]);
    }

    protected function createProduct(string $identifier, array $userIntents): void
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('peter'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->messageBus->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearDoctrineUoW();
    }

    protected function createProductModel(string $code, string $familyVariantCode, array $data): ProductModelInterface
    {
        $data = \array_merge(['code' => $code, 'family_variant' => $familyVariantCode], $data);
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = array_merge(['code' => $code], $data);

        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);

        $violations = $this->get('validator')->validate($family);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createFamilyVariant(string $code, string $family, array $data = []): FamilyVariantInterface
    {
        $data = array_merge(['code' => $code, 'family' => $family], $data);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $violations = $this->get('validator')->validate($familyVariant);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createAttributeOptions(string $attributeCode, array $optionsCodes): void
    {
        $attributeOptions = [];
        foreach ($optionsCodes as $optionCode) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => $optionCode,
                'attribute' => $attributeCode,
            ]);
            $attributeOptions[] = $attributeOption;
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }

    protected function createUser(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (\in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (\in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $violations = $this->get('validator')->validate($user);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }

    protected function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /**
     * @return array<string>
     */
    protected function getAssociatedProductIdentifiers(ProductInterface $product): array
    {
        return $product->getAssociatedProducts('X_SELL')
                ?->map(fn (ProductInterface $product): string => $product->getIdentifier())
                ?->toArray() ?? [];
    }
}
