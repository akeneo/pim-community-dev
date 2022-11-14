<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

class SetIdentifiersSubscriberEndToEnd extends TestCase
{
    private UserInterface $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();

        ($this->getCreateGeneratorHandler())(new CreateGeneratorCommand(
            'my_generator',
            [],
            [
                ['type' => 'free_text', 'string' => 'AKN'],
                ['type' => 'auto_number', 'numberMin' => 50, 'digitsMin' => 3],
            ],
            ['en_US' => 'My Generator'],
            'sku',
            '-'
        ));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_should_generate_an_identifier_on_create(): void
    {
        $product = $this->getProductBuilder()->createProduct();
        $this->getProductSaver()->save($product);

        /** @var ProductInterface $productFromDatabase */
        $productFromDatabase = $this->getProductRepository()->find($product->getUuid());
        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_an_identifier_when_deleting_previous_identifier(): void
    {
        $this->getAuthenticator()->logIn('admin');
        $product = $this->getProductBuilder()->createProduct('originalIdentifier');
        $this->getProductSaver()->save($product);

        $command = UpsertProductCommand::createWithUuid(
            $this->admin->getId(),
            ProductUuid::fromUuid($product->getUuid()),
            [
                new SetIdentifierValue('sku', null),
            ]
        );
        ($this->getUpsertProductHandler())($command);

        $productFromDatabase = $this->getProductRepository()->find($product->getUuid());
        Assert::assertSame('AKN-050', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-050', $productFromDatabase->getValue('sku')->getData());
    }

    /** @test */
    public function it_should_generate_the_next_identifier_if_there_is_already_one_created(): void
    {
        $existingProduct = $this->getProductBuilder()->createProduct('AKN-050');
        $this->getProductSaver()->save($existingProduct);

        $product = $this->getProductBuilder()->createProduct();
        $this->getProductSaver()->save($product);

        /** @var ProductInterface $productFromDatabase */
        $productFromDatabase = $this->getProductRepository()->find($product->getUuid());
        Assert::assertSame('AKN-051', $productFromDatabase->getIdentifier());
        Assert::assertSame('AKN-051', $productFromDatabase->getValue('sku')->getData());
    }

    private function getProductSaver(): ProductSaver
    {
        return $this->get('pim_catalog.saver.product');
    }

    private function getProductBuilder(): ProductBuilderInterface
    {
        return $this->get('pim_catalog.builder.product');
    }

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    private function getCreateGeneratorHandler(): CreateGeneratorHandler
    {
        return $this->get('Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler');
    }

    private function getUpsertProductHandler(): UpsertProductHandler
    {
        return $this->get('Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler');
    }

    private function getAuthenticator(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }
}
