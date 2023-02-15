<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\ProductMappingSchema;

use Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\DeleteProductMappingSchemaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\ProductMappingSchema\DeleteProductMappingSchemaQuery
 */
class DeleteProductMappingSchemaQueryTest extends IntegrationTestCase
{
    private ?DeleteProductMappingSchemaQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(DeleteProductMappingSchemaQuery::class);
    }

    public function testItDeletesProductMappingSchema(): void
    {
    }
}
