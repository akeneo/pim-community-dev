<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetProductUuidFromIdentifierQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetProductUuidFromIdentifierQuery
 */
class GetProductUuidFromIdentifierQueryTest extends IntegrationTestCase
{
    private ?GetProductUuidFromIdentifierQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetProductUuidFromIdentifierQuery::class);
    }

    public function testItGetsProductUuidFromIdentifier(): void
    {
        $user = $this->createUser('shopifi');
        $product = $this->createProduct('green', [], $user->getId());
        $expected = $product->getUuid();

        $result = $this->query->execute('green');

        $this->assertEquals($expected, $result);
    }
}
