<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Family;

use Akeneo\Catalogs\Application\Persistence\Family\SearchFamilyQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SearchFamilyQuery implements SearchFamilyQueryInterface
{
    public function __construct(
        private SearchableRepositoryInterface $searchableFamilyRepository,
    ) {
    }

    /**
     * @return array<array{code: string, label: string}>
     */
    public function execute(?string $search = null, int $page = 1, int $limit = 20): array
    {
        $families = $this->searchableFamilyRepository->findBySearch(
            $search,
            [
                'limit' => $limit,
                'page' => $page,
            ],
        );

        return \array_map(
            static fn (FamilyInterface $family) => ['code' => $family->getCode(), 'label' => $family->getLabel()],
            $families,
        );
    }
}
