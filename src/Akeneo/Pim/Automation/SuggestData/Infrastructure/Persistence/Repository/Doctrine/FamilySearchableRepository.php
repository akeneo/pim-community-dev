<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilySearchableRepository implements FamilySearchableRepositoryInterface
{
    /** @var SearchableRepositoryInterface */
    private $familyRepository;

    /**
     * @param SearchableRepositoryInterface $familyRepository
     */
    public function __construct(SearchableRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch(int $page, int $limit, ?string $search = null, array $identifiers = []): array
    {
        return $this->familyRepository->findBySearch($search, [
            'page' => $page,
            'limit' => $limit,
            'identifiers' => $identifiers,
        ]);
    }
}
