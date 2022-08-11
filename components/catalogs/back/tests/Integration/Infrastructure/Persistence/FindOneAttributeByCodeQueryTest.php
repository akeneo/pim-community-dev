<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\FindOneAttributeByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\FindOneAttributeByCodeQuery
 */
class FindOneAttributeByCodeQueryTest extends IntegrationTestCase
{
    private ?FindOneAttributeByCodeQuery $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(FindOneAttributeByCodeQuery::class);
    }

    public function testItReturnsTheNormalizedAttribute(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);

        $result = $this->query->execute('name');

        $this->assertEquals([
            'code' => 'name',
            'label' => '[name]',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ], $result);
    }

    public function testItReturnsNullIfNotFound(): void
    {
        $result = $this->query->execute('unknown');

        $this->assertNull($result);
    }
}
