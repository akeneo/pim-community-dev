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

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Filter\ChainedFilter;
use Pim\Bundle\EnrichBundle\Controller\CategoryTreeController as BaseCategoryTreeController;
use Pim\Component\Classification\Factory\CategoryFactory;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

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

    /** @var ChainedFilter */
    protected $chainedFilter;

    /**
     * @param Request                     $request
     * @param EngineInterface             $templating
     * @param RouterInterface             $router
     * @param SecurityContextInterface    $securityContext
     * @param FormFactoryInterface        $formFactory
     * @param ValidatorInterface          $validator
     * @param TranslatorInterface         $translator
     * @param EventDispatcherInterface    $eventDispatcher
     * @param ManagerRegistry             $doctrine
     * @param CategoryManager             $categoryManager
     * @param UserContext                 $userContext
     * @param SecurityFacade              $securityFacade
     * @param SaverInterface              $categorySaver
     * @param RemoverInterface            $categoryRemover
     * @param CategoryFactory             $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ChainedFilter               $chainedFilter
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        CategoryManager $categoryManager,
        UserContext $userContext,
        SecurityFacade $securityFacade,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        ChainedFilter $chainedFilter
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine,
            $categoryManager,
            $userContext,
            $securityFacade,
            $categorySaver,
            $categoryRemover,
            $categoryFactory,
            $categoryRepository
        );

        $this->chainedFilter = $chainedFilter;
    }

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
        } elseif (false === $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)) {
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
        $selectNodeId  = $request->get('select_node_id', -1);
        $context       = $request->get('context', false);
        $relatedEntity = $request->get('related_entity', 'product');

        try {
            $selectNode = $this->findGrantedCategory($selectNodeId, $context);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getAccessibleUserCategoryTree($relatedEntity);
        } catch (AccessDeniedException $e) {
            $selectNode = $this->userContext->getAccessibleUserCategoryTree($relatedEntity);
        }

        // TODO: In PIM-4292, check the right permissions depending on $context
        $trees = $this->categoryRepository->getTrees();
        $grantedTrees = $this->chainedFilter->filterCollection(
            $trees,
            sprintf('pim.internal_api.%s_category.view', $relatedEntity)
        );

        return [
            'trees'          => $grantedTrees,
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
            'item_count'     => (bool) $this->getRequest()->get('with_items_count', true),
            'related_entity' => $relatedEntity,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Override parent to use only granted categories
     */
    protected function getChildrenCategories(Request $request, $selectNode)
    {
        $categories = parent::getChildrenCategories($request, $selectNode);

        $context = $request->get('context', false);
        $relatedEntity = $request->get('related_entity', 'product');
        $editGranted = $this->securityFacade->isGranted('pim_enrich_product_category_edit');

        if ($editGranted && self::CONTEXT_MANAGE === $context) {
            return $categories;
        } else {
            return $this->chainedFilter->filterCollection(
                $categories,
                sprintf('pim.internal_api.%s_category.view', $relatedEntity)
            );
        }
    }
}
