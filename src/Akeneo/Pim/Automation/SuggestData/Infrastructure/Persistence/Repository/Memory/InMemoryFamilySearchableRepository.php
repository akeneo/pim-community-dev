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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InMemoryFamilySearchableRepository implements FamilySearchableRepositoryInterface
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch(int $page, int $limit, ?string $search = null, array $identifiers = []): array
    {
        $families = $this->familyRepository->findAll();

        $families = $this->applyPagination($families, $page, $limit);
        $families = $this->applySearchFilter($families, $search);

        return $families;
    }

    /**
     * @param array $families
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    private function applyPagination(array $families, int $page, int $limit): array
    {
        $families = new \LimitIterator(
            new \ArrayIterator($families),
            $page,
            $limit
        );

        return iterator_to_array($families, false);
    }

    /**
     * @param array $families
     * @param null|string $search
     *
     * @return array
     */
    private function applySearchFilter(array $families, ?string $search): array
    {
        if (empty($search)) {
            return $families;
        }

        $families = array_filter($families, function (Family $family) use ($search) {
            if ($this->stringContains($family->getCode(), $search)
                || $this->stringContains($family->getLabel(), $search)
            ) {
                return true;
            }

            return false;
        });

        return $families;
    }

    /**
     * @param string $string
     * @param $search
     *
     * @return bool
     */
    private function stringContains(string $string, $search): bool
    {
        return false !== strpos($string, $search);
    }
}
