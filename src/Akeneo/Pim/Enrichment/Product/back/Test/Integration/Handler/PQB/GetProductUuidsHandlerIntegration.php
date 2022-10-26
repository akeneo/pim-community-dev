<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert as WebmozartAssert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandlerIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');

        $this->commandMessageBus->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test1'), [])
        );
        $this->commandMessageBus->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test2'), [
                new SetTextValue('a_text', null, null, 'foo')
            ])
        );
        $this->refreshIndex();
    }

    /** @test */
    public function it_returns_a_product_uuids_cursor(): void
    {
        $dateInThePast = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');
        $productUuidCursor = $this->launchPQBCommand(['updated' => [['operator' => '>', 'value' => $dateInThePast]]]);

        $uuids = [];
        foreach ($productUuidCursor as $uuid) {
            $uuids[] = $uuid->toString();
        }

        Assert::assertCount(2, $uuids);
        Assert::assertContains($this->getProductUuid('test1')->toString(), $uuids);
        Assert::assertContains($this->getProductUuid('test2')->toString(), $uuids);
    }

    /** @test */
    public function it_filters_on_text_attribute(): void
    {
        $productUuidCursor = $this->launchPQBCommand(['a_text' => [['operator' => Operators::EQUALS, 'value' => 'foo']]]);

        $uuids = [];
        foreach ($productUuidCursor as $uuid) {
            $uuids[] = $uuid->toString();
        }

        Assert::assertCount(1, $uuids);
        Assert::assertContains($this->getProductUuid('test2')->toString(), $uuids);

        $productUuidCursor = $this->launchPQBCommand(['a_text' => [['operator' => Operators::EQUALS, 'value' => 'bar']]]);
        Assert::assertCount(0, $productUuidCursor);
    }

    /** @test */
    public function it_should_not_return_any_product(): void
    {
        $dateInTheFuture = (new \DateTime('now'))->modify("+ 30 minutes")->format('Y-m-d H:i:s');
        $productUuidCursor = $this->launchPQBCommand(['updated' => [['operator' => '>', 'value' => $dateInTheFuture]]]);
        Assert::assertCount(0, $productUuidCursor);
    }

    /** @test */
    public function it_returns_a_results_with_search_after(): void
    {
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test3'), []));
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test4'), []));
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test5'), []));
        $this->commandMessageBus->dispatch(UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier('test6'), []));
        $this->refreshIndex();

        $dateInThePast = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');
        $productUuidCursor = $this->launchPQBCommand(['updated' => [['operator' => '>', 'value' => $dateInThePast]]]);

        $uuids = [];
        foreach ($productUuidCursor as $uuid) {
            $uuids[] = $uuid->toString();
        }
        Assert::assertCount(6, $productUuidCursor);
        sort($uuids);

        $productUuidCursor = $this->launchPQBCommand(
            ['updated' => [['operator' => '>', 'value' => $dateInThePast]]],
            Uuid::fromString($uuids[2])
        );
        $paginatedUuids = [];
        foreach ($productUuidCursor as $uuid) {
            $paginatedUuids[] = $uuid->toString();
        }
        Assert::assertSame(\array_slice($uuids, 3), $paginatedUuids);
    }

    /** @test */
    public function it_throws_an_exception_when_the_user_does_not_exist(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('The "0" user does not exist');

        $dateInTheFuture = (new \DateTime('now'))->modify("+ 30 minutes")->format('Y-m-d H:i:s');
        $this->queryMessageBus->dispatch(new GetProductUuidsQuery(
            ['updated' => [['operator' => '>', 'value' => $dateInTheFuture]]],
            0
        ));
    }

    /** @test */
    public function it_throws_an_exception_when_the_search_filters_are_not_valid(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Structure of filter "updated" should respect this structure: {"updated":[{"operator": "my_operator", "value": "my_value"}]}');

        $dateInTheFuture = (new \DateTime('now'))->modify("+ 30 minutes")->format('Y-m-d H:i:s');
        $this->launchPQBCommand(['updated' => $dateInTheFuture]);
    }

    private function launchPQBCommand(array $search, ?UuidInterface $searchAfterUuid = null): ProductUuidCursor
    {
        $envelope = $this->queryMessageBus->dispatch(new GetProductUuidsQuery($search, null, $searchAfterUuid));
        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::assertNotNull($handledStamp, 'The bus does not return any result');

        $productUuidCursor = $handledStamp->getResult();
        Assert::assertInstanceOf(ProductUuidCursor::class, $productUuidCursor);

        return $productUuidCursor;
    }
}
