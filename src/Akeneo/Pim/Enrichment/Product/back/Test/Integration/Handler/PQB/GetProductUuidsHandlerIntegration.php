<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler\PQB;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\Application\PQB\ProductUuidCursor;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandlerIntegration extends EnrichmentProductTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

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
        $currentDate = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');
        $search = ['updated' => [['operator' => '>', 'value' => $currentDate]]];

        $envelope = $this->queryMessageBus->dispatch(new GetProductUuids($search));
        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::assertNotNull($handledStamp, 'The bus does not return any result');

        $productUuidCursor = $handledStamp->getResult();
        Assert::assertInstanceOf(ProductUuidCursor::class, $productUuidCursor);

        $uuids = [];
        foreach ($productUuidCursor as $uuid) {
            $uuids[] = $uuid->toString();
        }

        print_r($uuids);
    }
}
