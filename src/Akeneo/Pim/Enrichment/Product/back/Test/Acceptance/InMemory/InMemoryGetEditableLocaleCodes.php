<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Channel\Locale\API\Query\GetEditableLocaleCodes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetEditableLocaleCodes implements GetEditableLocaleCodes
{
    /** @var array<string, string[]> */
    private array $editableLocalesPerUserGroupName = [];

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forUserId(int $userId): array
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
                $this->editableLocalesPerUserGroupName[$groupName] ?? []
            );
        }

        return \array_unique($ownedCategoryCodes);
    }

    public function addOwnedCategoryCode(string $groupName, string $localeCode): void
    {
        $this->editableLocalesPerUserGroupName[$groupName][] = $localeCode;
    }
}
