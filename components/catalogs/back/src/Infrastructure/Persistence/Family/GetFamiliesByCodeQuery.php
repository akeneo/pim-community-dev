<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Family;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamiliesByCodeQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFamiliesByCodeQuery implements GetFamiliesByCodeQueryInterface
{
    public function __construct(
        private SearchableRepositoryInterface $searchableFamilyRepository,
    ) {
    }

    /**
     * @param array<string> $codes
     * @return array<array{code: string, label: string}>
     */
    public function execute(array $codes, int $page = 1, int $limit = 20): array
    {
        $families = $this->searchableFamilyRepository->findBySearch(
            null,
            [
                'identifiers' => $codes,
                'limit' => $limit,
                'page' => $page,
            ],
        );

        return \array_map(
            static fn (FamilyInterface $family): array => ['code' => $family->getCode(), 'label' => $family->getLabel()],
            $families,
        );
    }
}
