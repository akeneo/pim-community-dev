<?php
namespace Pim\Bundle\ProductBundle\Controller;

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
     * Get classification tree manager
     *
     * @return \Oro\Bundle\SegmentationTreeBundle\Model\SegmentManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_product.classification_tree_manager');
    }

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
        $manager = $this->getTreeManager();

        $segments = $this->getDoctrine()->getEntityManager()
                    ->getRepository('Pim\Bundle\ProductBundle\Entity\ProductSegment')
                    ->findAll();

        return array('segments' => $segments);
    }

    /**
     * List classification trees
     *
     * @Route("/list-tree")
     * @Template("PimProductBundle:ClassificationTree:listTree.html.twig")
     *
     * @return array
     */
    public function listTreeAction()
    {
        $trees = $this->getTreeManager()->getTrees();

        return array('trees' => $trees);
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
}
