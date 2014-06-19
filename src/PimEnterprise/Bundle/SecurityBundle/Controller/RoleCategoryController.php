<?php

namespace PimEnterprise\Bundle\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryOwnershipManager;

/**
 * Controller to list categories for a role
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RoleCategoryController
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * @var CategoryOwnershipManager
     */
    protected $ownershipManager;

    /**
     * Constructor
     *
     * @param CategoryManager          $categoryManager
     * @param CategoryOwnershipManager $ownershipManager
     */
    public function __construct(CategoryManager $categoryManager, CategoryOwnershipManager $ownershipManager)
    {
        $this->categoryManager  = $categoryManager;
        $this->ownershipManager = $ownershipManager;
    }

    /**
     * List categories for a role
     *
     * @param Request           $request
     * @param Role              $role
     * @param CategoryInterface $tree
     *
     * @ParamConverter("tree", class="PimCatalogBundle:Category", options={"id" = "tree_id"})
     * @Template("PimEnrichBundle:Product:listCategories.json.twig")
     * @AclAncestor("oro_user_role_update")
     *
     * @return array
     */
    public function listCategoriesAction(Request $request, Role $role, CategoryInterface $tree)
    {
        $categories = $this->ownershipManager->getOwnedCategories($role);

        $trees = $this->categoryManager->getFilledTree($tree, $categories);

        return ['trees' => $trees, 'categories' => $categories];
    }
}
