<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\IsUserOwnerOnAllCategoriesQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectCategoryCodesByProductGridFiltersQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IsUserOwnerOnAllProductsController
{
    /** @var SelectCategoryCodesByProductGridFiltersQueryInterface */
    private $findCategoriesQuery;

    /** @var IsUserOwnerOnAllCategoriesQueryInterface */
    private $isUserOwnerOnAllCategoriesQuery;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        SelectCategoryCodesByProductGridFiltersQueryInterface $findCategoriesQuery,
        IsUserOwnerOnAllCategoriesQueryInterface $isUserOwnerOnAllCategoriesQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->findCategoriesQuery = $findCategoriesQuery;
        $this->isUserOwnerOnAllCategoriesQuery = $isUserOwnerOnAllCategoriesQuery;
        $this->tokenStorage = $tokenStorage;
    }

    public function checkAction(Request $request)
    {
        $username = $this->tokenStorage->getToken()->getUsername();

        $filters = $request->request->get('filters', null);
        if (empty($filters)) {
            throw new UnprocessableEntityHttpException('Filters should not be empty');
        }

        $categories = $this->findCategoriesQuery->execute($filters);
        $isUserOwnerOnAllProducts = $this->isUserOwnerOnAllCategoriesQuery->execute($username, $categories);

        return new JsonResponse($isUserOwnerOnAllProducts);
    }
}
