<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Channel;

use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelQuery
 */
class GetChannelQueryTest extends IntegrationTestCase
{
    private ?GetChannelQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetChannelQuery::class);
    }

    public function testItGetsChannelByCode(): void
    {
        $this->createChannel('mobile', ['en_US']);

        $result = $this->query->execute('mobile');

        $expected = [
            'code' => 'mobile',
            'label' => '[mobile]',
        ];

        self::assertEquals($expected, $result);
    }

    public function testItGetsNullWithInvalidCode(): void
    {
        $result = $this->query->execute('not_a_channel_code');

        self::assertNull($result);
    }
}
