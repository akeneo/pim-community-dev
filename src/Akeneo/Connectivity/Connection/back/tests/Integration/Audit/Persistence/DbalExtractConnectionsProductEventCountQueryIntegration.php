<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\ExtractConnectionsProductEventCountQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\DbalExtractConnectionsProductEventCountQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalExtractConnectionsProductEventCountQueryIntegration extends TestCase
{
    private ?ConnectionLoader $connectionLoader;
    private ?ExtractConnectionsProductEventCountQueryInterface $extractConnectionsProductEventCountQuery;
    private ?DbalConnection $dbalConnection;
    private ?string $productClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->extractConnectionsProductEventCountQuery = $this->get(DbalExtractConnectionsProductEventCountQuery::class);
        $this->dbalConnection = self::getContainer()->get('database_connection');
        $this->productClass = self::getContainer()->getParameter('pim_catalog.entity.product.class');
        $this->client = self::getContainer()->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->productMessageBus = self::getContainer()->get('pim_enrich.product.message_bus');
        $this->productRepository = self::getContainer()->get('pim_catalog.repository.product');
    }

    public function test_it_extracts_created_products_by_connection(): void
    {
        $erpConnection = $this->connectionLoader
            ->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $notAuditableConnection = $this->connectionLoader
            ->createConnection('not_auditable', 'Not auditable', FlowType::DATA_SOURCE, false);
        $sapConnection = $this->connectionLoader
            ->createConnection('sap', 'SAP', FlowType::DATA_SOURCE, true);

        $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hourlyInterval = HourlyInterval::createFromDateTime($dateTime);

        $erpProduct1 = $this->createProduct('erp_product1', [new SetEnabled(false)]);
        $erpProduct2 = $this->createProduct('erp_product2', [new SetEnabled(true)]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct1, $dateTime);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct2, $dateTime);

        $notAuditableConnectionProduct1 = $this->createProduct('not_auditable_connection_product1', [new SetEnabled(true)]);
        $this->setVersioningAuthorAndDate($notAuditableConnection->username(), $notAuditableConnectionProduct1, $dateTime);

        $sapProduct1 = $this->createProduct('sap_product1', [new SetEnabled(false)]);
        $this->setVersioningAuthorAndDate($sapConnection->username(), $sapProduct1, $dateTime);

        $result = $this->extractConnectionsProductEventCountQuery->extractCreatedProductsByConnection($hourlyInterval);

        $expectedResult = [
            new HourlyEventCount('erp', $hourlyInterval, 2, EventTypes::PRODUCT_CREATED),
            new HourlyEventCount('sap', $hourlyInterval, 1, EventTypes::PRODUCT_CREATED),
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    public function test_it_extracts_updated_products_by_connection(): void
    {
        $erpConnection = $this->connectionLoader
            ->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $notAuditableConnection = $this->connectionLoader
            ->createConnection('not_auditable', 'Not auditable', FlowType::DATA_SOURCE, false);

        $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $hourlyInterval = HourlyInterval::createFromDateTime($dateTime);

        $erpProduct1 = $this->createProduct('erp_product1', [new SetEnabled(false)]);
        $erpProduct2 = $this->createProduct('erp_product2', [new SetEnabled(true)]);
        $this->createProduct('erp_product1', [new SetEnabled(true)]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct1, $dateTime);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct2, $dateTime);

        $notAuditableConnectionProduct1 = $this->createProduct('not_auditable_connection_product1', [new SetEnabled(true)]);
        $this->updateProduct('not_auditable_connection_product1', [new SetEnabled(true)]);
        $this->setVersioningAuthorAndDate($notAuditableConnection->username(), $notAuditableConnectionProduct1, $dateTime);

        $result = $this->extractConnectionsProductEventCountQuery->extractUpdatedProductsByConnection($hourlyInterval);

        $expectedResult = [
            new HourlyEventCount('erp', $hourlyInterval, 1, EventTypes::PRODUCT_UPDATED),
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    private function createProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        return $this->createOrUpdateProduct($identifier, $userIntents);
    }

    private function updateProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        return $this->createOrUpdateProduct($identifier, $userIntents);
    }

    private function setVersioningAuthorAndDate(
        string $author,
        ?ProductInterface $product = null,
        ?\DateTimeImmutable $dateTime = null
    ): void {
        if (null === $dateTime) {
            $dateTime = new \DateTimeImmutable('1 hour ago', new \DateTimeZone('UTC'));
        }

        $sqlQuery = <<<SQL
UPDATE pim_versioning_version
SET author = :author, logged_at = :logged_at
WHERE resource_name = :resource_name
SQL;
        $parameters = [
            'author' => $author,
            'resource_name' => $this->productClass,
            'logged_at' => $dateTime,
        ];

        if (null !== $product) {
            $sqlQuery .= <<<SQL
 AND resource_uuid = :product_uuid
SQL;
            $parameters['product_uuid'] = $product->getUuid()->getBytes();
        }

        $this->dbalConnection->executeQuery(
            $sqlQuery,
            $parameters,
            [
                'logged_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createOrUpdateProduct(string $identifier, array $userIntents = []) : ProductInterface
    {
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        $this->productMessageBus->dispatch(
            UpsertProductCommand::createWithIdentifierSystemUser($identifier, $userIntents)
        );

        return $this->productRepository->findOneByIdentifier($identifier);
    }
}
