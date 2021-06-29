<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var ExtractConnectionsProductEventCountQuery */
    private $extractConnectionsProductEventCountQuery;

    /** @var DbalConnection */
    private $dbalConnection;

    /** @var string */
    private $productClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->extractConnectionsProductEventCountQuery = $this->get('akeneo_connectivity.connection.persistence.query.extract_connections_product_event_count');
        $this->dbalConnection = self::$container->get('database_connection');
        $this->productClass = self::$container->getParameter('pim_catalog.entity.product.class');
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

        $erpProduct1 = $this->createProduct('erp_product1', ['enabled' => false]);
        $erpProduct2 = $this->createProduct('erp_product2', ['enabled' => true]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct1, $dateTime);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct2, $dateTime);

        $notAuditableConnectionProduct1 = $this->createProduct('not_auditable_connection_product1', ['enabled' => true]);
        $this->setVersioningAuthorAndDate($notAuditableConnection->username(), $notAuditableConnectionProduct1, $dateTime);

        $sapProduct1 = $this->createProduct('sap_product1', ['enabled' => false]);
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

        $erpProduct1 = $this->createProduct('erp_product1', ['enabled' => false]);
        $erpProduct2 = $this->createProduct('erp_product2', ['enabled' => true]);
        $this->updateProduct($erpProduct1, ['enabled' => true]);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct1, $dateTime);
        $this->setVersioningAuthorAndDate($erpConnection->username(), $erpProduct2, $dateTime);

        $notAuditableConnectionProduct1 = $this->createProduct('not_auditable_connection_product1', ['enabled' => true]);
        $this->updateProduct($notAuditableConnectionProduct1, ['enabled' => true]);
        $this->setVersioningAuthorAndDate($notAuditableConnection->username(), $notAuditableConnectionProduct1, $dateTime);

        $result = $this->extractConnectionsProductEventCountQuery->extractUpdatedProductsByConnection($hourlyInterval);

        $expectedResult = [
            new HourlyEventCount('erp', $hourlyInterval, 1, EventTypes::PRODUCT_UPDATED),
        ];

        Assert::assertEquals($expectedResult, $result);
    }

    private function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->updateProduct($product, $data);

        return $product;
    }

    private function updateProduct(ProductInterface $product, array $data = []): ProductInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
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
 AND resource_id = :product_id
SQL;
            $parameters['product_id'] = $product->getId();
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
}
