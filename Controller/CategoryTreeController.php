<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\HttpFoundation\Response;

use Pim\Bundle\ProductBundle\Helper\CategoryHelper;

use Pim\Bundle\ProductBundle\Form\Type\CategoryType;
use Pim\Bundle\ProductBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/category-tree")
 */
class CategoryTreeController extends Controller
{

    /**
     * Index action
     *
     * @Route("/")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     *
     * @Route("/list-tree.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function listTreeAction()
    {
        $selectNodeId = $this->getRequest()->get('select_node_id');
        $selectNode = null;

        if ($selectNodeId != null) {
            try {
                $selectNode = $this->findCategory($selectNodeId);
            } catch (NotFoundHttpException $e) {
                $selectNode = null;
            }
        }

        $trees = $this->getTreeManager()->getTrees();

        $treesResponse = CategoryHelper::treesResponse($trees, $selectNode);

        return array('trees' => $treesResponse);
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     *
     * @Route("/children.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function childrenAction()
    {
        $selectNodeId = $this->getRequest()->get('select_node_id');
        $selectNode = null;

        if ($selectNodeId != null) {
            try {
                $selectNode = $this->findCategory($selectNodeId);
            } catch (NotFoundHttpException $e) {
                $selectNode = null;
            }
        }

        try {
            $category = $this->findCategory($this->getRequest()->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('data' => array());
        }

        if (($selectNode != null)
            && (!$this->getTreeManager()->isAncestor($category, $selectNode))) {
            $selectNode = null;
        }

        if ($selectNode != null) {
            $categories = $this->getTreeManager()->getChildren($category->getId(), $selectNode->getId());
            $data = CategoryHelper::childrenTreeResponse($categories, $selectNode);
        } else {
            $categories = $this->getTreeManager()->getChildren($category->getId());
            $data = CategoryHelper::childrenResponse($categories);
        }

        return array('data' => $data);
    }


    /**
     * Find a category from its id
     *
     * @param integer $categoryId
     *
     * @return Category
     */
    protected function findCategory($categoryId)
    {
        $category = $this->getTreeManager()->getEntityRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * List products associated with the provided category
     *
     * @param Request $request Request (category_id)
     *
     * @Route("/list-items.{_format}/{id}", requirements={"_format"="json", "id"="\d+"})
     * @Template()
     *
     * @return array
     *
     */
    public function listItemsAction(Category $category)
    {
        $products = new ArrayCollection();

        if (is_object($category)) {
            $products = $category->getProducts();
        }

        $data = CategoryHelper::productsResponse($products);

        return array('data' => $data);
    }

    /**
     * Show tree in management mode
     *
     * @param Category $treeRoot
     *
     * @Route(
     *     "/manage/{treeRoot}",
     *     requirements={"treeRoot"="\d+"},
     *     defaults={"treeRoot"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:manage.html.twig")
     *
     * @return array
     */
    public function manageAction(Category $treeRoot)
    {
        $categories = $this->getTreeManager()->getTreeCategories($treeRoot);

        return array('categories' => $categories);
    }

    /**
     * Create category action
     *
     * @param Category $parent
     *
     * @Route(
     *     "/create/{parent}",
     *     requirements={"parent"="\d+"},
     *     defaults={"parent"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:edit.html.twig")
     *
     * @return array
     */
    public function createAction(Category $parent = null)
    {
        if ($parent === null) {
            $category = $this->getTreeManager()->getTreeInstance();
        } else {
            $category = $this->getTreeManager()->getSegmentInstance();
            $category->setParent($parent);
        }

        return $this->editAction($category);
    }

    /**
     * Edit tree action
     *
     * @param Category $category The category to manage
     *
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:edit.html.twig")
     *
     * @return array
     */
    public function editAction(Category $category)
    {
        $request = $this->getRequest();
        $form = $this->createForm(new CategoryType(), $category);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getTreeManager()->getStorageManager()->persist($category);
                $this->getTreeManager()->getStorageManager()->flush();

                $nodeType = $category->getParent() ? 'Category' : 'Tree';
                $this->get('session')->getFlashBag()->add('success', $nodeType. ' successfully saved');

                return $this->redirect(
                    $this->generateUrl(
                        'pim_product_categorytree_index',
                        array('node' => $category->getId())
                    )
                );
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Remove category tree
     *
     * @param Category $category The category to delete
     *
     * @Route(
     *     "/{id}/remove.{_format}",
     *     requirements={"_format"="json|html", "id"="\d+"},
     *     defaults={"_format"="html", "id"="\d+"}
     * )
     * @Template()
     *
     * @return array
     */
    public function removeAction(Category $category)
    {
        $count = $this->getTreeManager()->getEntityRepository()->countProductsLinked($category, false);

        if ($count == 0) {
            $this->getTreeManager()->remove($category);
            $this->getTreeManager()->getStorageManager()->flush();
        } else {
            return new JsonResponse('They are products in this category, but they will not be deleted', 500);
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse();
        } else {
            $this->get('session')->getFlashBag()->add('success', 'Category successfully removed');

            return $this->redirect($this->generateUrl('pim_product_categorytree_index'));
        }
    }

    /**
     * Get category tree manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\CategoryManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_product.category_manager');
    }

    /**
     * Get category path
     *
     * @param Category $category
     *
     * @return multitype:integer
     */
    protected function getCategoryPath(Category $category)
    {
        return $this->getTreeManager()->getEntityRepository()->getPath($category);
    }
}
