<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Query;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Cache\LRUCacheGetProductUuids;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsIntegration extends TestCase
{
    private UuidInterface $uuidFoo;
    private UuidInterface $uuidBar;
    private int $adminUserId;
    private SqlGetProductUuids $sqlQuery;
    private LRUCacheGetProductUuids $cachedQuery;

    /** @test */
    public function it_returns_uuid_for_product_identifier(): void
    {
        Assert::assertEquals($this->uuidFoo, $this->sqlQuery->fromIdentifier('foo'));
        Assert::assertEquals($this->uuidFoo, $this->cachedQuery->fromIdentifier('foo'));
    }

    /** @test */
    public function it_returns_null_for_non_existing_product(): void
    {
        Assert::assertNull($this->sqlQuery->fromIdentifier('non_existing_product'));
        Assert::assertNull($this->cachedQuery->fromIdentifier('non_existing_product'));
    }

    /** @test */
    public function it_returns_uuids_by_identifiers_for_product_uuids()
    {
        $expected = [
            'foo' => $this->uuidFoo,
            'bar' => $this->uuidBar,
        ];

        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->sqlQuery->fromIdentifiers(['foo', 'non_existing_product', 'bar'])
        );
        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->cachedQuery->fromIdentifiers(['foo', 'non_existing_product', 'bar'])
        );
    }

    /** @test */
    public function it_returns_an_existing_product_uuid(): void
    {
        Assert::assertEquals($this->uuidFoo, $this->sqlQuery->fromUuid($this->uuidFoo));
        Assert::assertEquals($this->uuidFoo, $this->cachedQuery->fromUuid($this->uuidFoo));
    }

    /** @test */
    public function it_returns_null_when_a_uuid_does_not_exist_yet(): void
    {
        Assert::assertNull($this->sqlQuery->fromUuid(Uuid::uuid4()));
        Assert::assertNull($this->cachedQuery->fromUuid(Uuid::uuid4()));
    }

    /** @test */
    public function it_returns_existing_product_uuids(): void
    {
        $expected = [
            $this->uuidFoo->toString() => $this->uuidFoo,
            $this->uuidBar->toString() => $this->uuidBar,
        ];
        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->sqlQuery->fromUuids([$this->uuidFoo, Uuid::uuid4(), $this->uuidBar])
        );
        Assert::assertEqualsCanonicalizing(
            $expected,
            $this->cachedQuery->fromUuids([$this->uuidFoo, Uuid::uuid4(), $this->uuidBar])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->uuidFoo = Uuid::uuid4();
        $this->uuidBar = Uuid::uuid4();
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->adminUserId = (int) $this->get('database_connection')->fetchOne(
            'SELECT id from oro_user WHERE username = \'admin\''
        );
        $this->sqlQuery = $this->get('Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids');
        $this->cachedQuery = $this->get('Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids');
        $this->createProduct($this->uuidFoo, 'foo');
        $this->createProduct($this->uuidBar, 'bar');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createProduct(UuidInterface $uuid, string $identifier): void
    {
        $identifierAttribute = $this->get('pim_catalog.repository.attribute')->getIdentifierCode();
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminUserId,
                ProductUuid::fromUuid($uuid),
                [new SetIdentifierValue($identifierAttribute, $identifier)],
            )
        );
    }
}
