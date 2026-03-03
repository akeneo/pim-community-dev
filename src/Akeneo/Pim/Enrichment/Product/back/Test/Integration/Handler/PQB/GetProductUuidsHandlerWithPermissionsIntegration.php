<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidCursor;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandlerWithPermissionsIntegration extends EnrichmentProductTestCase
{
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        FeatureHelper::skipIntegrationTestWhenPermissionFeatureIsNotAvailable();

        parent::setUp();
        $this->get('feature_flags')->enable('permission');
        $this->productRepository = $this->get('pim_catalog.repository.product');

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->createProduct('product_always_visible', []);
        $this->createProduct('product_visible_by_manager', [
            new SetCategories(['print']),
        ]);
        $this->createProduct('product_not_visible_by_manager', [
            new SetCategories(['suppliers']),
        ]);
        $this->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_returns_a_product_uuids_cursor(): void
    {
        $dateInThePast = (new \DateTime('now'))->modify("- 30 minutes")->format('Y-m-d H:i:s');
        $productUuidCursor = $this->launchPQBCommand(['updated' => [['operator' => '>', 'value' => $dateInThePast]]], 'betty');

        $uuids = [];
        foreach ($productUuidCursor as $uuid) {
            $uuids[] = $uuid->toString();
        }

        Assert::assertCount(2, $uuids);
        Assert::assertContains($this->getProductUuid('product_always_visible')->toString(), $uuids);
        Assert::assertContains($this->getProductUuid('product_visible_by_manager')->toString(), $uuids);
        Assert::assertNotContains($this->getProductUuid('product_not_visible_by_manager')->toString(), $uuids);
    }

    private function launchPQBCommand(array $search, string $username): ProductUuidCursor
    {
        $envelope = $this->queryMessageBus->dispatch(new GetProductUuidsQuery($search, $this->getUserId($username)));
        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::assertNotNull($handledStamp, 'The bus does not return any result');

        $productUuidCursor = $handledStamp->getResult();
        Assert::assertInstanceOf(ProductUuidCursor::class, $productUuidCursor);

        return $productUuidCursor;
    }
}
