<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\AttributeCriterion;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\FindOneAttributeByCodeQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Attribute\GetAttributeOptionsByCodeQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetChannelLocalesQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetLocalesQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Measurement\GetMeasurementsFamilyQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeCriterionTest extends IntegrationTestCase
{
    protected ?FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery;
    protected ?GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery;
    protected ?GetChannelQueryInterface $getChannelQuery;
    protected ?GetLocalesQueryInterface $getLocalesQuery;
    protected ?GetChannelLocalesQueryInterface $getChannelLocalesQuery;
    protected ?GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery;

    private array $attributes = [];
    private array $attributeOptions = [];
    private array $channels = [];
    private array $channelLocales = [];
    private array $locales = [];
    private array $measurementsFamily = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::purgeData();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributes = [];
        $this->attributeOptions = [];
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

        $this->getAttributeOptionsByCodeQuery = $this->createMock(GetAttributeOptionsByCodeQueryInterface::class);
        $this->getAttributeOptionsByCodeQuery
            ->method('execute')
            ->willReturnCallback(
                function ($code, $options): array {
                    $intersection = \array_filter(
                        $this->attributeOptions[$code] ?? [],
                        static fn ($option): bool => \in_array($option, $options)
                    );

                    return \array_map(static fn ($option): array => [
                        'code' => $option,
                        'label' => '['.$option.']',
                    ], $intersection);
                }
            );
        self::getContainer()->set(GetAttributeOptionsByCodeQuery::class, $this->getAttributeOptionsByCodeQuery);

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

        $this->getMeasurementsFamilyQuery = $this->createMock(GetMeasurementsFamilyQueryInterface::class);
        $this->getMeasurementsFamilyQuery
            ->method('execute')
            ->willReturnCallback(fn (string $code, string $locale): ?array => $this->measurementsFamily[$code] ?? null);
        self::getContainer()->set(GetMeasurementsFamilyQuery::class, $this->getMeasurementsFamilyQuery);
    }

    protected function createAttribute(array $data): void
    {
        $this->attributeOptions[$data['code']] = $data['options'] ?? [];
        unset($data['options']);

        $this->attributes[$data['code']] = $data;
    }

    protected function createChannel(string $code, array $locales = [], array $currencies = ['USD']): void
    {
        $this->channels[$code] = [
            'code' => $code,
            'label' => $code,
        ];
        $this->channelLocales[$code] = $locales;
    }

    protected function createMeasurementsFamily(array $data): void
    {
        $this->measurementsFamily[$data['code']] = [
            'code' => $data['code'],
            'units' => $data['units'],
        ];
    }
}
