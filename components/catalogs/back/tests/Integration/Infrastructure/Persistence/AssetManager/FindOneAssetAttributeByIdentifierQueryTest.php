<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\AssetManager;

use Akeneo\Catalogs\Infrastructure\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQuery
 */
class FindOneAssetAttributeByIdentifierQueryTest extends IntegrationTestCase
{
    private ?FindOneAssetAttributeByIdentifierQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(FindOneAssetAttributeByIdentifierQuery::class);
    }

    /**
     * @group ce
     */
    public function testItReturnsNull(): void
    {
        $result = $this->query->execute('name');

        $this->assertNull($result);
    }
}
