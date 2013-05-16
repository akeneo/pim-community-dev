<?php
namespace Pim\Bundle\ProductBundle\Controller;

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
        $families = $this->getProductFamilyRepository()->findAllOrderedByName();

        return array('families' => $families);
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
        $family = new ProductFamily();

        return $this->editAction($family);
    }

    /**
     * Edit product family
     *
     * @param ProductFamily $family
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
    public function editAction($id)
    {
        $family   = $this->getProductFamilyRepository()->findOneWithAttributes($id);
        if (!$family) {
            throw $this->createNotFoundException(sprintf(
                'Couldn\'t find a product family with id %d', $id
            ));
        }
        $families = $this->getProductFamilyRepository()->findAllOrderedByName();
        $request = $this->getRequest();
        $form    = $this->createForm(new ProductFamilyType(), $family);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($family);
                $this->getEntityManager()->flush();

                $this->get('session')->getFlashBag()->add('success', 'Product family successfully saved');

                return $this->redirect($this->generateUrl('pim_product_productfamily_edit', array('id' => $id)));
            }
        }

        return array(
            'form'           => $form->createView(),
            'families'       => $families,
            'family'         => $family,
            'attributesForm' => $this->getAvailableProductAttributesForm($family->getAttributes()->toArray())->createView()
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
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product family successfully removed');

        return $this->redirect($this->generateUrl('pim_product_productfamily_index'));
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Get product family repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductFamilyRepository()
    {
        return $this->getEntityManager()->getRepository('PimProductBundle:ProductFamily');
    }

}
