<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Pim\Bundle\ProductBundle\Helper\JsonSegmentHelper;

use Pim\Bundle\ProductBundle\Form\Type\ProductSegmentType;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/list-tree")
     *
     *
     * @TODO : response json format
     * @TODO : XML HTTP Request
     * @TODO : Use layout
     *
     * @return array
     */
    public function listTreeAction()
    {
        $trees = $this->getTreeManager()->getTrees();

        $data = JsonSegmentHelper::treesResponse($trees);

        return $this->prepareJsonResponse($data);
    }


    /**
     * Return a response in json content type with well formated data
     * @param mixed $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * TODO : Must be removed
     */
    protected function prepareJsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * List children of a segment
     *
     * @param ProductSegment $segment
     *
     * @Route("/children")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function childrenAction()
    {
        $parentId = (int) $this->getRequest()->get('id');

        if ($parentId <= 0) {
            throw new \InvalidArgumentException("Missing segment id parameter 'id':".$parentId);
        }

        $segments = $this->getTreeManager()->getChildren($parentId);

        $data = JsonSegmentHelper::childrenResponse($segments);

        return $this->prepareJsonResponse($data);
    }

    /**
     * List products associated with the provided segment
     *
     * @param Request $request Request (segment_id)
     *
     * @Route("/list-items")
     *
     * @return Response
     *
     */
    public function listItemsAction()
    {
        $segmentId = $this->getRequest()->get('segment_id');

        $repo = $this->getTreeManager()->getEntityRepository();
        $segment = $repo->find($segmentId);

        $products = new ArrayCollection();

        if (is_object($segment)) {
            $products = $segment->getProducts();
        }

        $data = JsonSegmentHelper::productsResponse($products);

        return $this->prepareJsonResponse($data);
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
     * Show tree in view mode
     *
     * @param ProductSegment $treeRoot
     *
     * @Route(
     *     "/view/{treeRoot}",
     *     requirements={"treeRoot"="\d+"},
     *     defaults={"treeRoot"=0}
     * )
     * @Template("PimProductBundle:ClassificationTree:manage.html.twig")
     *
     * @return array
     */
    public function viewAction(ProductSegment $treeRoot)
    {
        $segments = $this->getTreeManager()->getTreeSegments($treeRoot);

        // TODO : for each dynamic segment, get possible values and count products

        $unclassifiedNode = $this->getTreeManager()->getSegmentInstance();
        $unclassifiedNode->setParent($treeRoot);
        $unclassifiedNode->setIsDynamic(true);
        $unclassifiedNode->setTitle('Unclassified node');
        $unclassifiedNode->setCode('unclassified-node');
        $unclassifiedNode->setRoot($treeRoot->getId());

        $treeRoot->addChild($unclassifiedNode);

        $segments[] = $unclassifiedNode;

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
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(ProductSegment $segment)
    {
        $this->getTreeManager()->remove($segment);
        $this->getTreeManager()->getStorageManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product segment successfully removed');

        return $this->redirect($this->generateUrl('pim_product_classificationtree_index'));
    }

    /**
     * Get classification tree manager
     *
     * @return \Oro\Bundle\SegmentationTreeBundle\Model\SegmentManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_product.classification_tree_manager');
    }
}
