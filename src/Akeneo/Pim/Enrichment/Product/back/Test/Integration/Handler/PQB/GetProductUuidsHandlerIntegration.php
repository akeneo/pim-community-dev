<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\Stamp\HandledStamp;

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

        $this->commandMessageBus->dispatch(
            UpsertProductCommand::createFromCollection($this->getUserId('admin'), 'test1', [])
        );
        $this->commandMessageBus->dispatch(
            UpsertProductCommand::createFromCollection($this->getUserId('admin'), 'test2', [
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
        Assert::assertContains($this->getProductUuid('test1'), $uuids);
        Assert::assertContains($this->getProductUuid('test2'), $uuids);
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
        Assert::assertContains($this->getProductUuid('test2'), $uuids);

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

    private function launchPQBCommand(array $search): ProductUuidCursor
    {
        $envelope = $this->queryMessageBus->dispatch(new GetProductUuidsQuery($search));
        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::assertNotNull($handledStamp, 'The bus does not return any result');

        $productUuidCursor = $handledStamp->getResult();
        Assert::assertInstanceOf(ProductUuidCursor::class, $productUuidCursor);

        return $productUuidCursor;
    }

    private function getProductUuid(string $productIdentifier): string
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);
        Assert::assertNotNull($product);

        return $product->getUuid()->toString();
    }
}
