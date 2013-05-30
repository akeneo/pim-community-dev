<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Form\Type\ProductFamilyType;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

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
     * @Route("/")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('pim_product_productfamily_create'));
    }

    /**
     * Create product family
     *
     * @Route("/create")
     * @Template()
     *
     * @return array
     */
    public function createAction()
    {
        $family   = new ProductFamily;
        $form     = $this->createForm(new ProductFamilyType(), $family);
        $families = $this->getProductFamilyRepository()->findAllOrderedByName();
        $request  = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($family);
                $em->flush();

                $this->addFlash('success', 'Product family successfully created');

                return $this->redirect(
                    $this->generateUrl('pim_product_productfamily_edit', array('id' => $family->getId()))
                );
            }
        }

        return array(
            'form'     => $form->createView(),
            'families' => $families,
        );
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
        $family   = $this->findFamilyOr404($id);
        $families = $this->getProductFamilyRepository()->findAllOrderedByName();
        $request  = $this->getRequest();
        $form     = $this->createForm(new ProductFamilyType(), $family);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $this->getEntityManager()->persist($family);
                $this->getEntityManager()->flush();

                $this->addFlash('success', 'Product family successfully updated.');

                return $this->redirect($this->generateUrl('pim_product_productfamily_edit', array('id' => $id)));
            }

            $this->getEntityManager()->refresh($family);
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
     * @Method("DELETE")
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
     * Add attributes to a family
     *
     * @param int $id The family id to which add attributes
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/{id}/attributes", requirements={"id"="\d+", "_method"="POST"})
     *
     */
    public function addProductAttributes($id)
    {
        $family              = $this->findFamilyOr404($id);
        $availableAttributes = new AvailableProductAttributes;
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $family->getAttributes()->toArray(), $availableAttributes
        );

        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->getEntityManager()->flush();

        return $this->redirectToProductFamilyAttributesTab($family->getId());
    }

    /**
     * @Route("/{familyId}/attribute/{attributeId}")
     * @Method("DELETE")
     */
    public function removeProductAttribute($familyId, $attributeId)
    {
        $family    = $this->findFamilyOr404($familyId);
        $attribute = $this->findAttributeOr404($attributeId);

        if (false === $family->hasAttribute($attribute)) {
            throw $this->createNotFoundException(sprintf(
                'Attribute "%s" is not attached to "%s"',
                $attribute, $family
            ));
        }

        if ($attribute === $family->getAttributeAsLabel()) {
            $this->addFlash('error', 'You cannot remove this attribute because it\'s used as label for the family.');

            return $this->redirectToProductFamilyAttributesTab($family->getId());
        }

        $family->removeAttribute($attribute);
        $this->getEntityManager()->flush();

        $this->addFlash('success', 'The family is successfully updated.');

        return $this->redirectToProductFamilyAttributesTab($family->getId());
    }

    protected function redirectToProductFamilyAttributesTab($id)
    {
        return $this->redirect(sprintf('%s#attributes', $this->generateUrl(
            'pim_product_productfamily_edit', array('id' => $id)
        )));
    }

    protected function findFamilyOr404($id)
    {
        $family = $this->getProductFamilyRepository()->findOneWithAttributes($id);
        if (!$family) {
            throw $this->createNotFoundException(sprintf(
                'Couldn\'t find a product family with id %d', $id
            ));
        }

        return $family;
    }

    protected function findAttributeOr404($id)
    {
        $attribute = $this->getProductAttributeRepository()->findOneBy(array(
            'id' => $id
        ));
        if (!$attribute) {
            throw $this->createNotFoundException(sprintf(
                'Couldn\'t find a product family with id %d', $id
            ));
        }

        return $attribute;
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
