<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Handler;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\Api\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\UpsertProductHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PHPUnit\Framework\Assert;

final class UpsertProductWithPermissionIntegration extends TestCase
{
    private UpsertProductHandler $upsertProductHandler;
    private ProductRepositoryInterface $productRepository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        FeatureHelper::skipIntegrationTestWhenPermissionFeatureIsNotActivated();
        parent::setUp();

        $this->upsertProductHandler = $this->get(UpsertProductHandler::class);
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    /** @test */
    public function it_throws_an_exception_when_user_category_is_not_granted(): void
    {
        // Creates empty product (use command/handler when we can set a category)
        $product = $this->get('pim_catalog.builder.product')->createProduct('identifier');
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA'],
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->productRepository->findOneByIdentifier('identifier');
        Assert::assertNotNull($product);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product

        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('You don\'t have access to products in any tree, please contact your administrator');

        $command = new UpsertProductCommand(userId: $this->getUserId('mary'), productIdentifier: 'identifier', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        ($this->upsertProductHandler)($command);
    }

    /** @test */
    public function it_creates_a_new_uncategorized_product(): void
    {
        $command = new UpsertProductCommand(userId: $this->getUserId('mary'), productIdentifier: 'new_product', valuesUserIntent: [
            new SetTextValue('a_text', null, null, 'foo'),
        ]);
        ($this->upsertProductHandler)($command);

        $this->clearDoctrineUoW();
        $product = $this->productRepository->findOneByIdentifier('new_product');
        Assert::assertNotNull($product);
        Assert::assertSame('new_product', $product->getIdentifier());
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($user);

        return $user->getId();
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
