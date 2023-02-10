<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductMapping\AttributeSource;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetChannelLocalesQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeSourceTest extends IntegrationTestCase
{
    protected ?FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery;
    protected ?GetChannelQueryInterface $getChannelQuery;
    protected ?GetLocalesQueryInterface $getLocalesQuery;
    protected ?GetChannelLocalesQueryInterface $getChannelLocalesQuery;

    private array $attributes = [];
    private array $channels = [];
    private array $channelLocales = [];
    private array $locales = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::purgeData();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributes = [];
        $this->channels = [
            'ecommerce' => [
                'code' => 'ecommerce',
                'label' => 'E-commerce',
            ],
        ];
        $this->channelLocales = [
            'ecommerce' => ['en_US'],
        ];
        $this->locales = [
            'en_US' => [
                'code' => 'en_US',
                'label' => 'English',
            ],
            'fr_FR' => [
                'code' => 'fr_FR',
                'label' => 'French',
            ],
            'de_DE' => [
                'code' => 'de_DE',
                'label' => 'German',
            ],
        ];

        $this->findOneAttributeByCodeQuery = $this->createMock(FindOneAttributeByCodeQueryInterface::class);
        $this->findOneAttributeByCodeQuery
            ->method('execute')
            ->willReturnCallback(fn ($code) => $this->attributes[$code] ?? null);
        self::getContainer()->set(FindOneAttributeByCodeQuery::class, $this->findOneAttributeByCodeQuery);

        $this->getChannelQuery = $this->createMock(GetChannelQueryInterface::class);
        $this->getChannelQuery
            ->method('execute')
            ->willReturnCallback(fn ($code): ?array => $this->channels[$code] ?? null);
        self::getContainer()->set(GetChannelQuery::class, $this->getChannelQuery);

        $this->getLocalesQuery = $this->createMock(GetLocalesQueryInterface::class);
        $this->getLocalesQuery
            ->method('execute')
            ->willReturn(\array_values($this->locales));
        self::getContainer()->set(GetLocalesQuery::class, $this->getLocalesQuery);

        $this->getChannelLocalesQuery = $this->createMock(GetChannelLocalesQueryInterface::class);
        $this->getChannelLocalesQuery
            ->method('execute')
            ->willReturnCallback(function ($code): array {
                if (!isset($this->channelLocales[$code])) {
                    throw new \LogicException();
                }

                return \array_map(fn ($locale) => $this->locales[$locale], $this->channelLocales[$code]);
            });
        self::getContainer()->set(GetChannelLocalesQuery::class, $this->getChannelLocalesQuery);
    }

    protected function createAttribute(array $data): void
    {
        $this->attributes[$data['code']] = $data;
    }
}
