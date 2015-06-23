<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Controller;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Permission controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class PermissionRestController
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var AttributeGroupRepositoryInterface */
    protected $attGroupRepository;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param SecurityContextInterface          $securityContext
     * @param AttributeGroupRepositoryInterface $attGroupRepository
     * @param UserContext                       $userContext
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AttributeGroupRepositoryInterface $attGroupRepository,
        UserContext $userContext
    ) {
        $this->securityContext    = $securityContext;
        $this->attGroupRepository = $attGroupRepository;
        $this->userContext        = $userContext;
    }

    /**
     * @return JsonResponse
     */
    public function permissionsAction()
    {
        $securityContext = $this->securityContext;

        $locales = array_map(
            function ($locale) use ($securityContext) {
                return [
                    'code' => $locale->getCode(),
                    'view' => $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale),
                    'edit' => $securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)
                ];
            },
            $this->userContext->getUserLocales()
        );

        $attributeGroups = array_map(
            function ($group) use ($securityContext) {
                return [
                    'code' => $group->getCode(),
                    'view' => $securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $group),
                    'edit' => $securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $group)
                ];
            },
            $this->attGroupRepository->findAll()
        );

        return new JsonResponse(
            [
                'locales'          => $locales,
                'attribute_groups' => $attributeGroups,
            ]
        );
    }
}
