<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\CommandMessageBus;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;

abstract class EndToEndTestCase extends TestCase
{
    private UserInterface $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
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

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
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
