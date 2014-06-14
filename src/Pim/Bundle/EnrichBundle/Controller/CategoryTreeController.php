<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends AbstractDoctrineController
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param ManagerRegistry          $doctrine
     * @param CategoryManager          $categoryManager
     * @param UserContext              $userContext
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ManagerRegistry $doctrine,
        CategoryManager $categoryManager,
        UserContext $userContext
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->categoryManager = $categoryManager;
        $this->userContext     = $userContext;
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
        return $this->manageTreeAction($request);
    }

    /**
     * List category trees for management.The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     *
     * @param Request $request
     *
     * @return array
     *
     * @Template("PimEnrichBundle:CategoryTree:listTree.json.twig")
     * @AclAncestor("pim_enrich_category_list")
     */
    public function manageTreeAction(Request $request)
    {
        $selectNodeId = $request->get('select_node_id', -1);
        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getUserTree();
        }

        return array(
            'trees'          => $this->categoryManager->getTrees(),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
        );
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_category_edit")
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        $categoryId    = $request->get('id');
        $parentId      = $request->get('parent');
        $prevSiblingId = $request->get('prev_sibling');

        if ($request->get('copy') == 1) {
            $this->categoryManager->copy($categoryId, $parentId, $prevSiblingId);
        } else {
            $this->categoryManager->move($categoryId, $parentId, $prevSiblingId);
        }
        $this->categoryManager->getObjectManager()->flush();

        return new JsonResponse(array('status' => 1));
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_category_list")
     * @return array
     */
    public function childrenAction(Request $request)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('categories' => array());
        }

        $selectNodeId      = $this->getRequest()->get('select_node_id', -1);
        $withProductsCount = (bool) $this->getRequest()->get('with_products_count', false);
        $includeParent     = (bool) $this->getRequest()->get('include_parent', false);
        $includeSub        = (bool) $this->getRequest()->get('include_sub', false);

        try {
            $selectNode = $this->findCategory($selectNodeId);

            if (!$this->categoryManager->isAncestor($parent, $selectNode)) {
                $selectNode = null;
            }
        } catch (NotFoundHttpException $e) {
            $selectNode = null;
        }

        if ($selectNode !== null) {
            $categories = $this->categoryManager->getChildren($parent->getId(), $selectNode->getId());
            $view = 'PimEnrichBundle:CategoryTree:children-tree.json.twig';
        } else {
            $categories = $this->categoryManager->getChildren($parent->getId());
            $view = 'PimEnrichBundle:CategoryTree:children.json.twig';
        }

        return $this->render(
            $view,
            array(
                'categories'    => $categories,
                'parent'        => ($includeParent) ? $parent : null,
                'include_sub'   => $includeSub,
                'product_count' => $withProductsCount,
                'select_node'   => $selectNode
            ),
            new JsonResponse()
        );
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param integer $parent
     *
     * @AclAncestor("pim_enrich_category_create")
     * @return array
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

        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $this->persist($category, true);

                $this->addFlash('success', sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));

                return $this->redirectToRoute('pim_enrich_categorytree_edit', array('id' => $category->getId()));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Edit tree action
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_enrich_category_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $category = $this->findCategory($id);
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $this->persist($category, true);

                $this->addFlash('success', sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Remove category tree
     *
     * @param integer $id
     *
     * @AclAncestor("pim_enrich_category_remove")
     * @return RedirectResponse
     */
    public function removeAction($id)
    {
        $category = $this->findCategory($id);
        $parent = $category->getParent();
        $params = ($parent !== null) ? array('node' => $parent->getId()) : array();

        if (count($category->getChannels())) {
            throw new DeleteException($this->getTranslator()->trans('flash.tree.not removable'));
        }
        $this->categoryManager->remove($category);
        foreach ($this->doctrine->getManagers() as $manager) {
            $manager->flush();
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_categorytree_create', $params);
        }
    }

    /**
     * Find a category from its id
     *
     * @param integer $categoryId
     *
     * @return CategoryInterface
     * @throws NotFoundHttpException
     */
    protected function findCategory($categoryId)
    {
        $category = $this->categoryManager->getEntityRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * Gets the options for the form
     *
     * @param CategoryInterface $category
     *
     * @return array
     */
    protected function getFormOptions(CategoryInterface $category)
    {
        return array();
    }
}
