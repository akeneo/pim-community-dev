<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends AbstractDoctrineController
{
    /** @var CategoryManager */
    protected $categoryManager;

    /** @var UserContext */
    protected $userContext;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var SaverInterface */
    protected $categorySaver;

    /** @var RemoverInterface */
    protected $categoryRemover;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param CategoryManager          $categoryManager
     * @param UserContext              $userContext
     * @param SecurityFacade           $securityFacade
     * @param SaverInterface           $categorySaver
     * @param RemoverInterface         $categoryRemover
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        CategoryManager $categoryManager,
        UserContext $userContext,
        SecurityFacade $securityFacade,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->categoryManager = $categoryManager;
        $this->userContext     = $userContext;
        $this->securityFacade  = $securityFacade;
        $this->categorySaver   = $categorySaver;
        $this->categoryRemover = $categoryRemover;
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     *
     * @param Request $request
     *
     * @return array
     *
     * @Template
     * @AclAncestor("pim_enrich_category_list")
     */
    public function listTreeAction(Request $request)
    {
        $selectNodeId = $request->get('select_node_id', -1);
        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getUserTree();
        }

        return [
            'trees'          => $this->categoryManager->getTrees(),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
            'product_count'  => (bool) $this->getRequest()->get('with_products_count', true),
            'related_entity' => $this->getRequest()->get('related_entity', 'product')
        ];
    }

    /**
     * Move a node
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_category_edit")
     *
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        $categoryId    = $request->get('id');
        $parentId      = $request->get('parent');
        $prevSiblingId = $request->get('prev_sibling');

        $this->categoryManager->move($categoryId, $parentId, $prevSiblingId);

        return new JsonResponse(['status' => 1]);
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_category_list")
     *
     * @return array
     */
    public function childrenAction(Request $request)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            return ['categories' => []];
        }

        $selectNodeId      = $this->getRequest()->get('select_node_id', -1);
        $withProductsCount = (bool) $this->getRequest()->get('with_products_count', false);
        $includeParent     = (bool) $this->getRequest()->get('include_parent', false);
        $includeSub        = (bool) $this->getRequest()->get('include_sub', false);
        $relatedEntity     = $this->getRequest()->get('related_entity', 'product');

        try {
            $selectNode = $this->findCategory($selectNodeId);

            if (!$this->categoryManager->isAncestor($parent, $selectNode)) {
                $selectNode = null;
            }
        } catch (NotFoundHttpException $e) {
            $selectNode = null;
        }

        if ($selectNode !== null) {
            $categories = $this->getChildren($parent->getId(), $selectNode->getId());
            $view = 'PimEnrichBundle:CategoryTree:children-tree.json.twig';
        } else {
            $categories = $this->getChildren($parent->getId());
            $view = 'PimEnrichBundle:CategoryTree:children.json.twig';
        }

        return $this->render(
            $view,
            [
                'categories'     => $categories,
                'parent'         => ($includeParent) ? $parent : null,
                'include_sub'    => $includeSub,
                'product_count'  => $withProductsCount,
                'select_node'    => $selectNode,
                'related_entity' => $relatedEntity
            ],
            new JsonResponse()
        );
    }

    /**
     * @Template()
     * @AclAncestor("pim_enrich_category_list")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param int     $parent
     *
     * @AclAncestor("pim_enrich_category_create")
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request, $parent = null)
    {
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category = $this->categoryManager->getCategoryInstance();
            $category->setParent($parent);
        } else {
            $category = $this->categoryManager->getTreeInstance();
        }

        $category->setCode($request->get('label'));
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_CREATE, new GenericEvent($category));
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $this->addFlash('success', sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->eventDispatcher->dispatch(CategoryEvents::POST_CREATE, new GenericEvent($category));

                return $this->redirectToRoute('pim_enrich_categorytree_edit', ['id' => $category->getId()]);
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit tree action
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_category_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $category = $this->findCategory($id);
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_EDIT, new GenericEvent($category));
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $this->addFlash('success', sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->eventDispatcher->dispatch(CategoryEvents::POST_EDIT, new GenericEvent($category));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Remove category tree
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_category_remove")
     *
     * @return Response|RedirectResponse
     */
    public function removeAction($id)
    {
        $category = $this->findCategory($id);
        $parent = $category->getParent();
        $params = ($parent !== null) ? ['node' => $parent->getId()] : [];
        $this->categoryRemover->remove($category, ['flush' => true]);
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_categorytree_index', $params);
        }
    }

    /**
     * Find a category from its id
     *
     * @param int $categoryId
     *
     * @throws NotFoundHttpException
     *
     * @return CategoryInterface
     */
    protected function findCategory($categoryId)
    {
        $category = $this->categoryManager->getCategoryRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * Gets the options for the form
     *
     * @return array
     */
    protected function getFormOptions()
    {
        return [];
    }

    /**
     * @param int   $parentId
     * @param mixed $selectNodeId
     *
     * @return CategoryInterface[]
     */
    protected function getChildren($parentId, $selectNodeId = false)
    {
        return $this->categoryManager->getChildren($parentId, $selectNodeId);
    }
}
