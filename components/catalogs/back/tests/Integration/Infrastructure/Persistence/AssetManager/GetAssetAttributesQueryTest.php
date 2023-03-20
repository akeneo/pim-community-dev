<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\AssetManager;

use Akeneo\Catalogs\Infrastructure\Persistence\AssetManager\GetAssetAttributesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\AssetManager\GetAssetAttributesQuery
 */
class GetAssetAttributesQueryTest extends IntegrationTestCase
{
    private ?GetAssetAttributesQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(GetAssetAttributesQuery::class);
    }

    /**
     * @group ce
     */
    public function testItReturnsAnEmptyArray(): void
    {
        $result = $this->query->execute('t-shirt');

        $this->assertEquals([], $result);
    }
}
