<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Controller\CategoryTreeController as BaseCategoryTreeController;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Overriden category controller
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryTreeController extends BaseCategoryTreeController
{
    /** @staticvar string */
    const CONTEXT_MANAGE = 'manage';

    /** @staticvar string */
    const CONTEXT_VIEW = 'view';

    /** @staticvar string */
    const CONTEXT_ASSOCIATE = 'associate';

    /**
     * Find a category from its id, trows an exception if not found or not granted
     *
     * @param int    $categoryId the category id
     * @param string $context    the retrieving context
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     *
     * @return CategoryInterface
     */
    protected function findGrantedCategory($categoryId, $context)
    {
        $category = $this->findCategory($categoryId);
        $allowed = [self::CONTEXT_MANAGE, self::CONTEXT_VIEW, self::CONTEXT_ASSOCIATE];

        if (!in_array($context, $allowed)) {
            throw new AccessDeniedException('You can not access this category');
        }

        if ($context === self::CONTEXT_MANAGE && !$this->securityFacade->isGranted('pim_enrich_category_edit')) {
            throw new AccessDeniedException('You can not access this category');
        } elseif (false === $this->securityFacade->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
            throw new AccessDeniedException('You can not access this category');
        }

        return $category;
    }

    /**
     * Find granted trees
     *
     * @param UserInterface $user    the user
     * @param string        $context the retrieving context
     *
     * @return CategoryInterface[]
     */
    protected function findGrantedTrees(UserInterface $user, $context)
    {
        $allTrees = ($context === self::CONTEXT_MANAGE);

        if ($allTrees && $this->securityFacade->isGranted('pim_enrich_category_edit')) {
            return $this->categoryManager->getTrees($this->getUser());
        } else {
            return $this->categoryManager->getAccessibleTrees($this->getUser());
        }
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
        $context      = $request->get('context', false);
        try {
            $selectNode = $this->findGrantedCategory($selectNodeId, $context);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getAccessibleUserTree();
        } catch (AccessDeniedException $e) {
            $selectNode = $this->userContext->getAccessibleUserTree();
        }

        return array(
            'trees'          => $this->findGrantedTrees($this->getUser(), $context),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
            'product_count'  => (bool) $this->getRequest()->get('with_products_count', true),
            'related_entity' => $this->getRequest()->get('related_entity', 'product'),
        );
    }
    /**
     * {@inheritdoc}
     *
     * Override parent to use only granted categories
     */
    protected function getChildren($parentId, $selectNodeId = false)
    {
        $context = $this->request->get('context', false);
        $allTrees = ($context === self::CONTEXT_MANAGE);
        if ($allTrees && $this->securityFacade->isGranted('pim_enrich_category_edit')) {
            return $this->categoryManager->getChildren($parentId, $selectNodeId);
        } else {
            return $this->categoryManager->getGrantedChildren($parentId, $selectNodeId);
        }
    }
}
