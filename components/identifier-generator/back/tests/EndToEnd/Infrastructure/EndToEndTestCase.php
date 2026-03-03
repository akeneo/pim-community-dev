<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure;

use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\CommandMessageBus;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

abstract class EndToEndTestCase extends TestCase
{
    private UserInterface $admin;
    protected static string $DEFAULT_FAMILY = 'my_family';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
        $this->createFamily(self::$DEFAULT_FAMILY);
    }

    /**
     * @return ProductInterface[]
     */
    protected function createProducts(int $count): array
    {
        $uuids = [];
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $uuid = Uuid::uuid4();
            $uuids[] = $uuid;
            $newProduct = new Product($uuid->toString());
            $family = $this->getFamilyRepository()->findOneByIdentifier(self::$DEFAULT_FAMILY);
            $newProduct->setFamily($family);
            $products[] = $newProduct;
        }

        $this->getProductSaver()->saveAll($products);

        return \array_map(
            fn (UuidInterface $uuid): ProductInterface => $this->getProductRepository()->find($uuid),
            $uuids
        );
    }

    /**
     * @param UserIntent[]|null $userIntents
     */
    protected function createProduct(?string $identifier = null, ?bool $withFamily = true, ?array $userIntents = []): ProductInterface
    {
        $uuid = Uuid::uuid4();
        $this->getAuthenticator()->logIn('admin');

        if (null !== $identifier) {
            $userIntents[] = new SetIdentifierValue('sku', $identifier);
        }

        if ($withFamily) {
            $userIntents[] = new SetFamily(self::$DEFAULT_FAMILY);
        }
        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($uuid),
            $userIntents
        );
        $this->getCommandMessageBus()->dispatch($command);

        return $this->getProductRepository()->find($uuid);
    }

    protected function updateProductIdentifier(ProductInterface $product, ?string $identifier = null): ProductInterface
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($product->getUuid()),
            [
                new SetIdentifierValue('sku', $identifier),
            ]
        );
        $this->getCommandMessageBus()->dispatch($command);

        return $this->getProductRepository()->find($product->getUuid());
    }

    protected function setProductFamily(UuidInterface $uuid, string $familyCode): void
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($uuid),
            [new SetFamily($familyCode)]
        );
        $this->getCommandMessageBus()->dispatch($command);
    }

    protected function setSimpleSelectProductValue(UuidInterface $uuid): void
    {
        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($uuid),
            [new SetSimpleSelectValue('color', null, null, 'red')]
        );
        $this->getCommandMessageBus()->dispatch($command);
    }

    protected function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    private function getProductSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product');
    }

    private function getAuthenticator(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }

    private function getCommandMessageBus(): CommandMessageBus
    {
        return $this->get('pim_enrich.product.message_bus');
    }

    private function getFamilyRepository(): FamilyRepositoryInterface
    {
        return $this->getContainer()->get('pim_catalog.repository.family');
    }

    protected function createFamily(string $code): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();

        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => $code,
            'labels' => [],
        ]);
        $constraints = $this->get('validator')->validate($family);
        Assert::count($constraints, 0);
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
