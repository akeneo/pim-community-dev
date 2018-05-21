<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Controller\InternalApi;

use Doctrine\ORM\EntityRepository;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Permission controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class PermissionRestController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepo;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var UserContext */
    protected $userContext;

    /** @var EntityRepository */
    protected $jobInstanceRepo;

    /**
     * @param AuthorizationCheckerInterface     $authorizationChecker
     * @param AttributeGroupRepositoryInterface $attributeGroupRepo
     * @param CategoryAccessRepository          $categoryAccessRepo
     * @param UserContext                       $userContext
     * @param EntityRepository                  $jobInstanceRepo
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeGroupRepositoryInterface $attributeGroupRepo,
        CategoryAccessRepository $categoryAccessRepo,
        UserContext $userContext,
        EntityRepository $jobInstanceRepo
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeGroupRepo   = $attributeGroupRepo;
        $this->categoryAccessRepo   = $categoryAccessRepo;
        $this->userContext          = $userContext;
        $this->jobInstanceRepo      = $jobInstanceRepo;
    }

    /**
     * @return JsonResponse
     */
    public function permissionsAction()
    {
        $authorizationChecker = $this->authorizationChecker;

        $locales = array_map(
            function ($locale) use ($authorizationChecker) {
                return [
                    'code' => $locale->getCode(),
                    'view' => $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale),
                    'edit' => $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)
                ];
            },
            $this->userContext->getUserLocales()
        );

        $attributeGroups = array_map(
            function ($group) use ($authorizationChecker) {
                return [
                    'code' => $group->getCode(),
                    'view' => $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group),
                    'edit' => $authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $group)
                ];
            },
            $this->attributeGroupRepo->findAll()
        );

        $jobInstances = array_map(
            function ($jobInstance) use ($authorizationChecker) {
                return [
                    'code'    => $jobInstance->getCode(),
                    'execute' => $authorizationChecker->isGranted(Attributes::EXECUTE, $jobInstance),
                    'edit'    => $authorizationChecker->isGranted(Attributes::EDIT, $jobInstance)
                ];
            },
            $this->jobInstanceRepo->findAll()
        );

        $user = $this->userContext->getUser();

        $categories = [];
        $permissions = [
            Attributes::VIEW_ITEMS,
            Attributes::EDIT_ITEMS,
            Attributes::OWN_PRODUCTS
        ];
        foreach ($permissions as $permission) {
            $categories[$permission] = $this->categoryAccessRepo->getGrantedCategoryCodes($user, $permission);
        }

        return new JsonResponse(
            [
                'locales'          => $locales,
                'attribute_groups' => $attributeGroups,
                'categories'       => $categories,
                'job_instances'    => $jobInstances
            ]
        );
    }
}
