<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CategoryCodeFilterInterface;
use Akeneo\Pim\Permission\Component\Query\GetViewableCategoryCodesForUserInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategoryCodeViewFilter implements CategoryCodeFilterInterface
{
    private GetViewableCategoryCodesForUserInterface $getViewableCategoryCodesForUser;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        GetViewableCategoryCodesForUserInterface $getViewableCategoryCodesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getViewableCategoryCodesForUser = $getViewableCategoryCodesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    public function filter(array $codes): array
    {
        $userId = $this->getAuthenticatedUserId();

        return $this->getViewableCategoryCodesForUser->forCategoryCodes($codes, $userId);
    }

    private function getAuthenticatedUserId(): int
    {
        if (null === $this->tokenStorage->getToken() || null === $this->tokenStorage->getToken()->getUser()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User is not authenticated');
        }

        return $user->getId();
    }
}
