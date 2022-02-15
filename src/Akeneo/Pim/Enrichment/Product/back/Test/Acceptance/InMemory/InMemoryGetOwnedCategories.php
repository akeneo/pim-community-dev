<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetOwnedCategories implements GetOwnedCategories
{
    /** @var array<string, string[]> */
    private array $ownedCategoryCodesPerUserGroupName = [];

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(array $categoryCodes, int $userId): array
    {
        /** @var UserInterface $user */
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (null === $user) {
            return [];
        }

        $ownedCategoryCodes = [];
        $user->getGroupNames();
        foreach ($user->getGroupNames() as $groupName) {
            $ownedCategoryCodes = \array_merge(
                $ownedCategoryCodes,
                $this->ownedCategoryCodesPerUserGroupName[$groupName] ?? []
            );
        }

        return \array_unique($ownedCategoryCodes);
    }

    public function addOwnedCategoryCode(string $groupName, string $categoryCode): void
    {
//        if (!\array_key_exists($groupName, $this->ownedCategoryCodesPerUserGroupName)) {
//            $this->ownedCategoryCodesPerUserGroupName[$groupName] = [];
//        }

        $this->ownedCategoryCodesPerUserGroupName[$groupName][] = $categoryCode;
    }
}
