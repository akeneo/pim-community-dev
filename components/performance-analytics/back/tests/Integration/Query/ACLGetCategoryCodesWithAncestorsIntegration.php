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

namespace Akeneo\Test\PerformanceAnalytics\Integration\Query;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer\ACLGetCategoryCodesWithAncestors;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ACLGetCategoryCodesWithAncestorsIntegration extends TestCase
{
    private ACLGetCategoryCodesWithAncestors $aclGetCategoryCodesWithAncestors;
    private MessageBusInterface $messageBus;
    private ProductRepositoryInterface $productRepository;

    /**
     * {@inheritDoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->aclGetCategoryCodesWithAncestors = $this->get(ACLGetCategoryCodesWithAncestors::class);
        $this->messageBus = $this->get('pim_enrich.product.message_bus');
        $this->productRepository = $this->get('pim_catalog.repository.product');
    }

    public function testItReturnsCategoryCodesWithAncestors(): void
    {
        $this->createCategory(['code' => 'A']);
        $this->createCategory(['code' => 'A1', 'parent' => 'A']);
        $this->createCategory(['code' => 'A11', 'parent' => 'A1']);
        $this->createCategory(['code' => 'A12', 'parent' => 'A1']);
        $this->createCategory(['code' => 'A2', 'parent' => 'A']);
        $this->createCategory(['code' => 'A21', 'parent' => 'A2']);
        $this->createCategory(['code' => 'B']);
        $this->createCategory(['code' => 'B1', 'parent' => 'B']);
        $this->createCategory(['code' => 'B11', 'parent' => 'B1']);

        $uuid1 = $this->createProduct('identifier1', ['A11', 'B1'])->getUuid();
        $uuid2 = $this->createProduct('identifier2', ['A21', 'A1'])->getUuid();
        $uuid3 = $this->createProduct('identifier3', [])->getUuid();
        $unknownUuid = Uuid::uuid4();

        self::assertCount(0, $this->aclGetCategoryCodesWithAncestors->forProductUuids([]));

        $results = $this->aclGetCategoryCodesWithAncestors->forProductUuids([$uuid1, $uuid2, $uuid3, $unknownUuid]);
        self::assertArrayHasKey($uuid1->toString(), $results);
        self::assertArrayHasKey($uuid2->toString(), $results);
        self::assertArrayHasKey($uuid3->toString(), $results);
        self::assertArrayNotHasKey($unknownUuid->toString(), $results);
        self::assertCount(3, $results);

        self::assertSame(['A', 'A1', 'A11', 'B', 'B1'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid1));
        self::assertSame(['A', 'A1', 'A2', 'A21'], $this->getOrderedStringCategoryCodesForProduct($results, $uuid2));
        self::assertSame([], $results[$uuid3->toString()]);
    }

    /**
     * @param array<string, CategoryCode[]> $results
     * @return string[]
     */
    private function getOrderedStringCategoryCodesForProduct(array $results, UuidInterface $productUuid): array
    {
        $stringCategoryCodes = \array_map(
            static fn (CategoryCode $categoryCode): string => $categoryCode->toString(),
            $results[$productUuid->toString()]
        );
        \sort($stringCategoryCodes);

        return $stringCategoryCodes;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $violations = $this->get('validator')->validate($category);
        self::assertEmpty($violations, (string) $violations);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    /**
     * @param string[] $categories
     */
    private function createProduct(string $identifier, array $categories): ProductInterface
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: [new SetCategories($categories)]
        );
        $this->messageBus->dispatch($command);

        return $this->productRepository->findOneByIdentifier($identifier);
    }

    private function getUserId(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        self::assertNotNull($user);

        return $user->getId();
    }
}
