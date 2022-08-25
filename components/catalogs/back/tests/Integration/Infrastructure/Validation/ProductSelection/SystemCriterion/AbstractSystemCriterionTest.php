<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection\SystemCriterion;

use Akeneo\Catalogs\Application\Persistence\GetFamiliesByCodeQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\GetFamiliesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractSystemCriterionTest extends IntegrationTestCase
{
    protected ?GetFamiliesByCodeQueryInterface $getFamiliesByCodeQuery;

    private array $families = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->families = [];

        $this->getFamiliesByCodeQuery = $this->createMock(GetFamiliesByCodeQueryInterface::class);
        $this->getFamiliesByCodeQuery
            ->method('execute')
            ->willReturnCallback(function (array $codes, int $page = 1, int $limit = 20) : array {
                $filteredFamilies = \array_filter(
                    $this->families,
                    static fn (array $family) => \in_array($family['code'], $codes, true)
                );
                return \array_slice($filteredFamilies, ($page - 1) * $limit, $limit);
            });
        self::getContainer()->set(GetFamiliesByCodeQuery::class, $this->getFamiliesByCodeQuery);
    }

    protected function createFamily(array $familyData): void
    {
        $this->families[$familyData['code']] = $familyData;
    }
}
