<?php

namespace PimEnterprise\Bundle\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
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
     * @var RouterInterface
     */
    protected $router;

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
     * @param RouterInterface          $router
     * @param CategoryManager          $categoryManager
     * @param CategoryOwnershipManager $ownershipManager
     */
    public function __construct(
        RouterInterface $router,
        CategoryManager $categoryManager,
        CategoryOwnershipManager $ownershipManager
    ) {
        $this->router           = $router;
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
        $categories = $this->ownershipManager->getOwnedCategories($role, $tree);

        if ($categories->count() === 0) {
            return new RedirectResponse(
                $this->router->generate(
                    'pim_enrich_categorytree_children',
                    [
                        'id'             => $tree->getId(),
                        '_format'        => 'json',
                        'include_parent' => true,
                        'dataLocale'     => $request->get('dataLocale')
                    ]
                )
            );
        }

        $trees = $this->categoryManager->getFilledTree($tree, $categories);

        return ['trees' => $trees, 'categories' => $categories];
    }
}
