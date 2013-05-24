<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\HttpFoundation\Response;

use Pim\Bundle\ProductBundle\Helper\SegmentHelper;

use Pim\Bundle\ProductBundle\Form\Type\ProductSegmentType;
use Pim\Bundle\ProductBundle\Entity\ProductSegment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Classification Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/classification-tree")
 */
class ClassificationTreeController extends Controller
{

    /**
     * Index action
     *
     * @Route("/index")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * List classification trees
     *
     * @Route("/list-tree.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function listTreeAction()
    {
        $trees = $this->getTreeManager()->getTrees();

        return array('trees' => $trees);
    }

    /**
     * List children of a segment
     *
     * @param ProductSegment $segment
     *
     * @Route("/children.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function childrenAction()
    {
        try {
            $segment = $this->findSegment($this->getRequest()->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('data' => array());
        }

        $segments = $this->getTreeManager()->getChildren($segment->getId());

        $data = SegmentHelper::childrenResponse($segments);

        return array('data' => $data);
    }


    /**
     * Find a segment from its id
     *
     * @param integer $segmentId
     *
     * @return ProductSegment
     */
    protected function findSegment($segmentId)
    {
        $segment = $this->getTreeManager()->getEntityRepository()->find($segmentId);

        if (!$segment) {
            throw $this->createNotFoundException('Product Segment not found');
        }

        return $segment;
    }

    /**
     * List products associated with the provided segment
     *
     * @param Request $request Request (segment_id)
     *
     * @Route("/list-items.{_format}/{id}", requirements={"_format"="json", "id"="\d+"})
     * @Template()
     *
     * @return array
     *
     */
    public function listItemsAction(ProductSegment $segment)
    {
        $products = new ArrayCollection();

        if (is_object($segment)) {
            $products = $segment->getProducts();
        }

        $data = SegmentHelper::productsResponse($products);

        return array('data' => $data);
    }

    /**
     * Show tree in management mode
     *
     * @param ProductSegment $treeRoot
     *
     * @Route(
     *     "/manage/{treeRoot}",
     *     requirements={"treeRoot"="\d+"},
     *     defaults={"treeRoot"=0}
     * )
     * @Template("PimProductBundle:ClassificationTree:manage.html.twig")
     *
     * @return array
     */
    public function manageAction(ProductSegment $treeRoot)
    {
        $segments = $this->getTreeManager()->getTreeSegments($treeRoot);

        return array('segments' => $segments);
    }

    /**
     * Create segment action
     *
     * @param ProductSegment $parent
     *
     * @Route(
     *     "/create/{parent}",
     *     requirements={"parent"="\d+"},
     *     defaults={"parent"=0}
     * )
     * @Template("PimProductBundle:ClassificationTree:edit.html.twig")
     *
     * @return array
     */
    public function createAction(ProductSegment $parent = null)
    {
        if ($parent === null) {
            $segment = $this->getTreeManager()->getTreeInstance();
        } else {
            $segment = $this->getTreeManager()->getSegmentInstance();
            $segment->setParent($parent);
        }

        return $this->editAction($segment);
    }

    /**
     * Edit tree action
     *
     * @param ProductSegment $segment The segment to manage
     *
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
     * )
     * @Template("PimProductBundle:ClassificationTree:edit.html.twig")
     *
     * @return array
     */
    public function editAction(ProductSegment $segment)
    {
        $request = $this->getRequest();
        $form = $this->createForm(new ProductSegmentType(), $segment);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getTreeManager()->getStorageManager()->persist($segment);
                $this->getTreeManager()->getStorageManager()->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product segment successfully saved');

                return $this->redirect($this->generateUrl('pim_product_classificationtree_index'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Remove classification tree
     *
     * @param ProductSegment $segment The segment to delete
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
    public function removeAction(ProductSegment $segment)
    {
        $count = $this->getTreeManager()->getEntityRepository()->countProductsLinked($segment, false);

        if ($count == 0) {
            $this->getTreeManager()->remove($segment);
            $this->getTreeManager()->getStorageManager()->flush();
        } else {
            return new JsonResponse('They are products in this category, but they will not be deleted', 500);
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse();
        } else {
            $this->get('session')->getFlashBag()->add('success', 'Product segment successfully removed');

            return $this->redirect($this->generateUrl('pim_product_classificationtree_index'));
        }
    }

    /**
     * Get classification tree manager
     *
     * @return \Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_product.classification_tree_manager');
    }
}
