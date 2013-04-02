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
     * Create segment action
     *
     * @param string $mode
     *
     * @Route(
     *     "/create/{mode}",
     *     requirements={"mode"="node|tree"},
     *     defaults={"mode"="node"}
     * )
     * @Template("PimProductBundle:ClassificationTree:edit.html.twig")
     *
     * @return array
     */
    public function createAction($mode)
    {
        $segment = $this->getTreeManager()->getSegmentInstance();

        return $this->editAction($segment, $mode);
    }

    /**
     * Edit tree action
     *
     * @param ProductSegment $tree The segment to manage
     * @param string         $mode Define working with node or tree
     *
     * @Route(
     *     "/edit/{id}/{mode}",
     *     requirements={"id"="\d+", "mode"="node|tree"},
     *     defaults={"id"=0, "mode"="node"}
     * )
     * @Template("PimProductBundle:ClassificationTree:edit.html.twig")
     *
     * @return array
     */
    public function editAction(ProductSegment $tree, $mode)
    {
        $request = $this->getRequest();
        $form = $this->createForm(new ProductSegmentType($mode), $tree);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getDoctrine()->getEntityManager()->persist($tree);
                $this->getDoctrine()->getEntityManager()->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product segment successfully saved');
            }
        }

        return array(
            'form' => $form->createView(),
            'mode' => $mode
        );
    }

    /**
     * Remove classification tree
     *
     * @param ProductSegment $tree The root segment to delete
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     *
     * TODO : Remove method must call remove tree if necessary
     */
    public function removeAction(ProductSegment $tree)
    {
        $this->getDoctrine()->getEntityManager()->remove($tree);
        $this->getDoctrine()->getEntityManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product segment successfully removed');

        return $this->redirect($this->generateUrl('pim_product_classificationtree_index'));
    }
}
