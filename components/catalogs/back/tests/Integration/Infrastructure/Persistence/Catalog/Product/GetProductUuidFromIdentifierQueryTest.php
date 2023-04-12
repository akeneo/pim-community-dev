<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductUuidFromIdentifierQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductUuidFromIdentifierQuery
 */
class GetProductUuidFromIdentifierQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsProductUuidFromIdentifier(): void
    {
        $user = $this->createUser('shopifi');

        $this->logAs($user->getUserIdentifier());

        $product = $this->createProduct('tshirt-green', [], $user->getId());
        $expected = $product->getUuid();

        $result = self::getContainer()->get(GetProductUuidFromIdentifierQuery::class)->execute('tshirt-green');

        $this->assertEquals($expected, $result);
    }
}
