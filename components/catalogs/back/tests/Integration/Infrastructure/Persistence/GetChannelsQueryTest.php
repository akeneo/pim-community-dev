<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetChannelsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Channel\Infrastructure\Component\Saver\ChannelSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelsQueryTest extends IntegrationTestCase
{
    private ?GetChannelsQuery $query;
    private ?SimpleFactoryInterface $channelFactory;
    private ?ObjectUpdaterInterface $channelUpdater;
    private ValidatorInterface $validator;
    private ?ChannelSaverInterface $channelSaver;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetChannelsQuery::class);
        $this->connection = self::getContainer()->get(Connection::class);
        $this->channelFactory = self::getContainer()->get('pim_catalog.factory.channel');
        $this->channelUpdater = self::getContainer()->get('pim_catalog.updater.channel');
        $this->validator = self::getContainer()->get('validator');
        $this->channelSaver = self::getContainer()->get('pim_catalog.saver.channel');
    }

    public function testItGetsPaginatedChannelsWithLocales(): void
    {
        //Already existing as part of minimal catalog: ecommerce with en_US
        $this->createChannel('tablet', ['en_US', 'de_DE']);
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $page1 = $this->query->execute(1, 2);
        $page2 = $this->query->execute(2, 2);

        $expectedPage1 = [
            [
                'code' => 'ecommerce',
                'label'=> '[ecommerce]',
                'locales' => [
                    ['code' => 'en_US', 'label' => 'English (United States)'],
                ],
            ],
            [
                'code' => 'tablet',
                'label'=> '[tablet]',
                'locales' => [
                    ['code' => 'en_US', 'label' => 'English (United States)'],
                    ['code' => 'de_DE', 'label' => 'German (Germany)'],
                ],
            ],
        ];

        $expectedPage2 = [
            [
                'code' => 'mobile',
                'label'=> '[mobile]',
                'locales' => [
                    ['code' => 'en_US', 'label' => 'English (United States)'],
                    ['code' => 'fr_FR', 'label' => 'French (France)'],
                ],
            ]
        ];

        self::assertEquals($expectedPage1, $page1);
        self::assertEquals($expectedPage2, $page2);
    }

    public function testItGetsChannelByCode(): void
    {
        //Already existing as part of minimal catalog: ecommerce with en_US
        $this->createChannel('tablet', ['en_US', 'de_DE']);
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $result = $this->query->execute(1, 20, 'mobile');
        $page2 = $this->query->execute(2, 20, 'mobile');

        $expected = [
            [
                'code' => 'mobile',
                'label'=> '[mobile]',
                'locales' => [
                    ['code' => 'en_US', 'label' => 'English (United States)'],
                    ['code' => 'fr_FR', 'label' => 'French (France)'],
                ],
            ]
        ];

        self::assertEquals($expected, $result);
        self::assertEquals([], $page2);
    }

    public function testItGetsAnEmptyListWithInvalidCode(): void
    {
        $this->createChannel('tablet', ['en_US', 'de_DE']);
        $this->createChannel('mobile', ['en_US', 'fr_FR']);

        $result = $this->query->execute(1, 20, 'not_a_channel_code');

        self::assertEquals([], $result);
    }

    private function createChannel(string $code, array $locales = []): void
    {
        $channel = $this->channelFactory->create();
        $this->channelUpdater->update($channel, [
            'code' => $code,
            'locales' => $locales,
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ]);

        $violations = $this->validator->validate($channel);
        self::assertSame(0, $violations->count(), (string) $violations);

        $this->channelSaver->save($channel);
    }
}
