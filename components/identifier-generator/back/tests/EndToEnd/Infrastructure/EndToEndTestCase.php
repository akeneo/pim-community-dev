<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\CommandMessageBus;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class EndToEndTestCase extends TestCase
{
    private UserInterface $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
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
            $products[] = new Product($uuid->toString());
        }

        $this->getProductSaver()->saveAll($products);

        return \array_map(
            fn (UuidInterface $uuid): ProductInterface => $this->getProductRepository()->find($uuid),
            $uuids
        );
    }

    protected function createProduct(?string $identifier = null): ProductInterface
    {
        $uuid = Uuid::uuid4();
        $this->getAuthenticator()->logIn('admin');

        $userIntents = [];
        if (null !== $identifier) {
            $userIntents = [new SetIdentifierValue('sku', $identifier)];
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

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    private function getProductSaver(): ProductSaver
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
}
