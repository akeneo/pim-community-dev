<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Channel;

use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelsQuery
 */
class GetChannelsQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsPaginatedChannels(): void
    {
        //Already existing as part of minimal catalog: ecommerce with en_US
        $this->createChannel('tablet', ['en_US']);
        $this->createChannel('mobile', ['en_US']);

        $page1 = self::getContainer()->get(GetChannelsQuery::class)->execute(1, 2);
        $page2 = self::getContainer()->get(GetChannelsQuery::class)->execute(2, 2);

        $expectedPage1 = [
            [
                'code' => 'ecommerce',
                'label' => '[ecommerce]',
            ],
            [
                'code' => 'tablet',
                'label' => '[tablet]',
            ],
        ];

        $expectedPage2 = [
            [
                'code' => 'mobile',
                'label' => '[mobile]',
            ],
        ];

        self::assertEquals($expectedPage1, $page1);
        self::assertEquals($expectedPage2, $page2);
    }
}
