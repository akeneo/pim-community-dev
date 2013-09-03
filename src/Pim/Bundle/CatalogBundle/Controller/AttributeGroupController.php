<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;

/**
 * AttributeGroup controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_attribute_group",
 *      name="Attribute group manipulation",
 *      description="Attribute group manipulation",
 *      parent="pim_catalog"
 * )
 */
class AttributeGroupController extends Controller
{
    /**
     * Create attribute group
     *
     * @Template("PimCatalogBundle:AttributeGroup:edit.html.twig")
     * @Acl(
     *      id="pim_catalog_attribute_group_create",
     *      name="Create group",
     *      description="Create group",
     *      parent="pim_catalog_attribute_group"
     * )
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
     * @Acl(
     *      id="pim_catalog_attribute_group_edit",
     *      name="Edit group",
     *      description="Edit group",
     *      parent="pim_catalog_attribute_group"
     * )
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToNameOrderedBySortOrder();

        if ($this->get('pim_catalog.form.handler.attribute_group')->process($group)) {
            $this->addFlash('success', 'Attribute group successfully saved');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->get('pim_catalog.form.attribute_group')->createView(),
            'attributesForm' => $this->getAvailableProductAttributesForm($this->getGroupedAttributes())->createView()
        );
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     * @Acl(
     *      id="pim_catalog_attribute_group_sort",
     *      name="Sort groups",
     *      description="Sort groups",
     *      parent="pim_catalog_attribute_group"
     * )
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_attributegroup_create');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $group = $this->getRepository('PimCatalogBundle:AttributeGroup')->find((int) $id);
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
     * @param Request        $request
     * @param AttributeGroup $group
     * @Acl(
     *      id="pim_catalog_attribute_group_remove",
     *      name="Remove group",
     *      description="Remove group",
     *      parent="pim_catalog_attribute_group"
     * )
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, AttributeGroup $group)
    {
        $this->remove($group);

        $this->addFlash('success', 'Attribute group successfully removed');

        if ($request->get('_redirectBack')) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        return $this->redirectToRoute('pim_catalog_attributegroup_create');
    }

    /**
     * Add attributes to a group
     *
     * @param Request $request The request object
     * @param integer $id      The group id to add attributes to
     * @Acl(
     *      id="pim_catalog_attribute_group_add_attribute",
     *      name="Add attribute to group",
     *      description="Add attribute to group",
     *      parent="pim_catalog_attribute_group"
     * )
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction(Request $request, $id)
    {
        $group               = $this->findOr404('PimCatalogBundle:AttributeGroup', $id);
        $availableAttributes = new AvailableProductAttributes();

        $attributesForm      = $this->getAvailableProductAttributesForm(
            $this->getGroupedAttributes(),
            $availableAttributes
        );

        $attributesForm->bind($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $group->addAttribute($attribute);
        }

        $this->flush();

        return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
    }

    /**
     * Remove a product attribute
     *
     * @param integer $groupId
     * @param integer $attributeId
     * @Acl(
     *      id="pim_catalog_attribute_group_remove_attribute",
     *      name="Remove attribute from a group",
     *      description="Remove attribute from a group",
     *      parent="pim_catalog_attribute_group"
     * )
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProductAttributeAction($groupId, $attributeId)
    {
        $group     = $this->findOr404('PimCatalogBundle:AttributeGroup', $groupId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        $group->removeAttribute($attribute);
        $this->flush();

        $this->addFlash('success', 'Attribute group successfully updated.');

        return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));

    }

    /**
     * Get attributes that belong to a group
     *
     * @return array
     */
    protected function getGroupedAttributes()
    {
        return $this->getRepository('PimCatalogBundle:ProductAttribute')->findAllGrouped();
    }
}
