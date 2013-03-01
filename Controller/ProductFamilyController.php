<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Form\Type\ProductFamilyType;

/**
 * Product Controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product-family")
 */
class ProductFamilyController extends Controller
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
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('PimProductBundle:ProductFamily')->findAll();
        return array('entities' => $entities);
    }

    /**
     * Create product family
     *
     * @Route("/create")
     * @Template("PimProductBundle:ProductFamily:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $entity = new ProductFamily();

        return $this->editAction($entity);
    }

    /**
     * Edit product family
     *
     * @param ProductFamily $entity
     *
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
     * )
     * @Template()
     *
     * @return array
     */
    public function editAction(ProductFamily $entity)
    {
        $request = $this->getRequest();
        $form = $this->createForm(new ProductFamilyType(), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product family successfully saved');

                return $this->redirect($this->generateUrl('pim_product_productfamily_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Remove product family
     *
     * @param ProductFamily $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(ProductFamily $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product family successfully removed');

        return $this->redirect($this->generateUrl('pim_product_productfamily_index'));
    }
}
