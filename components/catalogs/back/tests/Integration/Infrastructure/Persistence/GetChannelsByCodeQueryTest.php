<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelsByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelsByCodeQuery
 */
class GetChannelsByCodeQueryTest extends IntegrationTestCase
{
    public ?object $connection;
    private ?GetChannelsByCodeQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetChannelsByCodeQuery::class);
    }

    public function testItGetsPaginatedChannelsByCode(): void
    {
        //Already existing as part of minimal catalog: ecommerce with en_US
        $this->createChannel('tablet', ['en_US']);
        $this->createChannel('mobile', ['en_US']);

        $page1 = $this->query->execute(['tablet', 'ecommerce'], 1, 1);
        $page2 = $this->query->execute(['tablet', 'ecommerce'], 2, 1);
        $page3 = $this->query->execute(['tablet', 'ecommerce'], 3, 1);

        $expectedPage1 = [
            [
                'code' => 'ecommerce',
                'label' => '[ecommerce]',
            ],
        ];
        $expectedPage2 = [
            [
                'code' => 'tablet',
                'label' => '[tablet]',
            ],
        ];
        $expectedPage3 = [];

        self::assertEquals($expectedPage1, $page1);
        self::assertEquals($expectedPage2, $page2);
        self::assertEquals($expectedPage3, $page3);
    }

    public function testItGetsNoChannels(): void
    {
        $page = $this->query->execute([], 1, 2);

        self::assertEquals([], $page);
    }
}
