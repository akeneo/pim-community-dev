<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

/**
 * AttributeGroup controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupController extends Controller
{
    /**
     * Create attribute group
     *
     * @Template("PimProductBundle:AttributeGroup:edit.html.twig")
     * @return array
     */
    public function createAction()
    {
        $group = new AttributeGroup();

        return $this->editAction($group);
    }

    /**
     * Edit attribute group
     *
     * @param AttributeGroup $group
     *
     * @Template
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        $groups = $this->getRepository('PimProductBundle:AttributeGroup')->getIdToNameOrderedBySortOrder();

        if ($this->get('pim_product.form.handler.attribute_group')->process($group)) {
            $this->addFlash('success', 'Attribute group successfully saved');

            return $this->redirectToRoute('pim_product_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups' => $groups,
            'group'  => $group,
            'form'   => $this->get('pim_product.form.attribute_group')->createView(),
            'attributesForm' => $this->getAvailableProductAttributesForm($this->getGroupedAttributes())->createView()
        );
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_product_attributegroup_create');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $group = $this->getRepository('PimProductBundle:AttributeGroup')->find((int) $id);
                if ($group) {
                    $group->setSortOrder((int) $sort);
                    $this->persist($group, false);
                }
            }
            $this->flush();

            return new Response(1);
        }

        return new Response(0);
    }

    /**
     * Remove attribute group
     *
     * @param AttributeGroup $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(AttributeGroup $group)
    {
        $this->remove($group);

        $this->addFlash('success', 'Attribute group successfully removed');

        $request = $this->getRequest();

        if ($request->get('_redirectBack')) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        return $this->redirectToRoute('pim_product_attributegroup_create');
    }

    /**
     * Add attributes to a group
     *
     * @param int $id The group id to add attributes to
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction($id)
    {
        $group               = $this->findOr404('PimProductBundle:AttributeGroup', $id);
        $availableAttributes = new AvailableProductAttributes();

        $attributesForm      = $this->getAvailableProductAttributesForm(
            $this->getGroupedAttributes(),
            $availableAttributes
        );

        $attributesForm->bind($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $group->addAttribute($attribute);
        }

        $this->flush();

        return $this->redirectToRoute('pim_product_attributegroup_edit', array('id' => $group->getId()));
    }

    /**
     * Remove a product attribute
     *
     * @param integer $groupId
     * @param integer $attributeId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProductAttributeAction($groupId, $attributeId)
    {
        $group     = $this->findOr404('PimProductBundle:AttributeGroup', $groupId);
        $attribute = $this->findOr404('PimProductBundle:ProductAttribute', $attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        $group->removeAttribute($attribute);
        $this->flush();

        $this->addFlash('success', 'Attribute group successfully updated.');

        return $this->redirectToRoute('pim_product_attributegroup_edit', array('id' => $group->getId()));

    }

    /**
     * Get attributes that belong to a group
     *
     * @return array
     */
    protected function getGroupedAttributes()
    {
        return $this->getRepository('PimProductBundle:ProductAttribute')->findAllGrouped();
    }
}
