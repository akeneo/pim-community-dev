<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

/**
 * Product family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/family")
 */
class FamilyController extends Controller
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
        return $this->redirect($this->generateUrl('pim_product_family_create'));
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
        $family   = new Family;
        $families = $this->getRepository('PimProductBundle:Family')->getIdToLabelOrderedByLabel();

        if ($this->get('pim_product.form.handler.family')->process($family)) {
            $this->addFlash('success', 'Product family successfully created');

            return $this->redirectToFamilyAttributesTab($family->getId());
        }

        return array(
            'form'     => $this->get('pim_product.form.family')->createView(),
            'families' => $families,
        );
    }

    /**
     * Edit product family
     *
     * @param integer $id
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
        $family   = $this->findOr404('PimProductBundle:Family', $id);
        $families = $this->getRepository('PimProductBundle:Family')->getIdToLabelOrderedByLabel();
        $datagrid = $this->getDataAuditDatagrid($family, 'pim_product_family_edit', array('id' => $family->getId()));

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render('OroGridBundle:Datagrid:list.json.php', array('datagrid' => $datagrid->createView()));
        }

        if ($this->get('pim_product.form.handler.family')->process($family)) {
            $this->addFlash('success', 'Product family successfully updated.');

            return $this->redirect($this->generateUrl('pim_product_family_edit', array('id' => $id)));
        }

        return array(
            'form'           => $this->get('pim_product.form.family')->createView(),
            'families'       => $families,
            'family'         => $family,
            'attributesForm' => $this->getAvailableProductAttributesForm(
                $family->getAttributes()->toArray()
            )->createView(),
            'datagrid'       => $datagrid->createView(),
        );
    }

    /**
     * Remove product family
     *
     * @param Family $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     * @Method("DELETE")
     *
     * @return array
     */
    public function removeAction(Family $entity)
    {
        $this->getManager()->remove($entity);
        $this->getManager()->flush();

        $this->addFlash('success', 'Product family successfully removed');

        return $this->redirect($this->generateUrl('pim_product_family_index'));
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
    public function addProductAttributesAction($id)
    {
        $family              = $this->findOr404('PimProductBundle:Family', $id);
        $availableAttributes = new AvailableProductAttributes;
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->getManager()->flush();

        return $this->redirectToFamilyAttributesTab($family->getId());
    }

    /**
     * Remove product attribute
     *
     * @param integer $familyId
     * @param integer $attributeId
     *
     * @Route("/{familyId}/attribute/{attributeId}/remove")
     * @Method("DELETE")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProductAttributeAction($familyId, $attributeId)
    {
        $family    = $this->findOr404('PimProductBundle:Family', $familyId);
        $attribute = $this->findOr404('PimProductBundle:ProductAttribute', $attributeId);

        if (false === $family->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $family)
            );
        }

        if ($attribute !== $family->getAttributeAsLabel()) {
            $family->removeAttribute($attribute);
            $this->getManager()->flush();

            $this->addFlash('success', 'The family is successfully updated.');
        } else {
            $this->addFlash('error', 'You cannot remove this attribute because it\'s used as label for the family.');
        }

        return $this->redirectToFamilyAttributesTab($family->getId());
    }

    /**
     * Return to attributes tab
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToFamilyAttributesTab($id)
    {
        return $this->redirect($this->generateUrl('pim_product_family_edit', array('id' => $id), false, 'attributes'));
    }
}
