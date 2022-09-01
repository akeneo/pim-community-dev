<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindProductIdentifierIntegration extends TestCase
{
    private string $fooUuid;

    /** @test */
    public function it_gets_the_identifier_of_a_product_from_its_uuid(): void
    {
        $findIdentifier = $this->get('Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier');
        Assert::assertSame(
            'foo',
            $findIdentifier->fromUuid($this->fooUuid)
        );
        $unknownId = Uuid::uuid4();
        Assert::assertNull($findIdentifier->fromUuid($unknownId->toString()));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $dbalConnection = $this->get('database_connection');
        $localeId = $dbalConnection->fetchOne('SELECT id FROM pim_catalog_locale LIMIT 1');

        $sqlInsert = <<<SQL
            INSERT INTO oro_user
            (username, email, ui_locale_id, salt, password, createdAt, updatedAt, timezone, properties, profile) VALUES
            ('user1', 'user1@test.com', :localeId, 'my_salt', 'my_password', '2019-09-09', '2019-09-09', 'UTC', '{}', NULL)
SQL;

        $dbalConnection->executeQuery($sqlInsert, ['localeId' => $localeId]);
        $userId = $this->createAdminUser()->getId();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection((int) $userId, 'foo', []);
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        Assert::assertNotNull($product);
        $this->fooUuid = $product->getUuid()->toString();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
