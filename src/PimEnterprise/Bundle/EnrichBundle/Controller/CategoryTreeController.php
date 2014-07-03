<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\EnrichBundle\Controller\CategoryTreeController as BaseCategoryTreeController;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryTreeController extends BaseCategoryTreeController
{
    /**
     * Find a category from its id
     *
     * @param integer $categoryId
     *
     * @return CategoryInterface
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    protected function findAccessibleCategory($categoryId)
    {
        $category = $this->findCategory($categoryId);

        if (false === $this->securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $category)) {
            throw new AccessDeniedException('You can not access this category');
        }

        return $category;
    }

    /**
     * {@inheritdoc}
     *
     * @Template("PimEnrichBundle:CategoryTree:listTree.json.twig")
     * @AclAncestor("pim_enrich_category_list")
     */
    public function listTreeAction(Request $request)
    {
        $selectNodeId = $request->get('select_node_id', -1);
        try {
            $selectNode = $this->findAccessibleCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getAccessibleUserTree();
        } catch (AccessDeniedException $e) {
            $selectNode = $this->userContext->getAccessibleUserTree();
        }

        return array(
            'trees'          => $this->categoryManager->getAccessibleTrees($this->getUser()),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
            'product_count'  => (bool) $this->getRequest()->get('with_products_count', true),
            'related_entity' => $this->getRequest()->get('related_entity', 'product'),
        );
    }
}
