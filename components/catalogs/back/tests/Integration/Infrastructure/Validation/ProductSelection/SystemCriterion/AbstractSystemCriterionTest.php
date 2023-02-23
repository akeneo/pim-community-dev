<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Family\GetFamiliesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoriesByCodeQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Channel\GetChannelQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Family\GetFamiliesByCodeQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Locale\GetChannelLocalesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractSystemCriterionTest extends IntegrationTestCase
{
    protected ?GetChannelQueryInterface $getChannelQuery = null;
    protected ?GetChannelLocalesQueryInterface $getChannelLocalesQuery = null;
    protected ?GetFamiliesByCodeQueryInterface $getFamiliesByCodeQuery = null;
    protected ?GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery = null;

    private array $channels = [];
    private array $channelLocales = [];
    private array $families = [];
    private array $categories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupChannels();
        $this->setupChannelLocales();
        $this->setupFamilies();
        $this->setupCategories();
    }

    protected function createFamily(array $data): void
    {
        $this->families[$data['code']] = $data;
    }

    protected function createCategory(array $data = []): void
    {
        $this->categories[$data['code']] = $data;
    }

    private function setupChannels(): void
    {
        $this->channels = [
            'ecommerce' => [
                'code' => 'ecommerce',
                'label' => 'E-commerce',
            ],
        ];

        $this->getChannelQuery = $this->createMock(GetChannelQueryInterface::class);
        $this->getChannelQuery
            ->method('execute')
            ->willReturnCallback(fn ($code): ?array => $this->channels[$code] ?? null);
        self::getContainer()->set(GetChannelQuery::class, $this->getChannelQuery);
    }

    private function setupChannelLocales(): void
    {
        $this->channelLocales = [
            'ecommerce' => [
                ['code' => 'en_US', 'label' => 'English'],
            ],
        ];

        $this->getChannelLocalesQuery = $this->createMock(GetChannelLocalesQueryInterface::class);
        $this->getChannelLocalesQuery
            ->method('execute')
            ->willReturnCallback(fn ($code) => $this->channelLocales[$code] ?? throw new \LogicException());
        self::getContainer()->set(GetChannelLocalesQuery::class, $this->getChannelLocalesQuery);
    }

    private function setupFamilies(): void
    {
        $this->families = [];

        $this->getFamiliesByCodeQuery = $this->createMock(GetFamiliesByCodeQueryInterface::class);
        $this->getFamiliesByCodeQuery
            ->method('execute')
            ->willReturnCallback(function (array $codes, int $page = 1, int $limit = 20): array {
                $filteredFamilies = \array_filter(
                    $this->families,
                    static fn (array $family): bool => \in_array($family['code'], $codes, true),
                );
                return \array_slice($filteredFamilies, ($page - 1) * $limit, $limit);
            });
        self::getContainer()->set(GetFamiliesByCodeQuery::class, $this->getFamiliesByCodeQuery);
    }

    private function setupCategories(): void
    {
        $this->categories = [];

        $this->getCategoriesByCodeQuery = $this->createMock(GetCategoriesByCodeQueryInterface::class);
        $this->getCategoriesByCodeQuery
            ->method('execute')
            ->willReturnCallback(fn (array $codes): array => \array_filter(
                $this->categories,
                static fn (array $category): bool => \in_array($category['code'], $codes, true),
            ));
        self::getContainer()->set(GetCategoriesByCodeQuery::class, $this->getCategoriesByCodeQuery);
    }
}
