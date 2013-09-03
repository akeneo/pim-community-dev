<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_family",
 *      name="Family manipulation",
 *      description="Family manipulation",
 *      parent="pim_catalog"
 * )
 */
class FamilyController extends Controller
{
    /**
     * Create a family
     *
     * @param Request $request
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_family_create",
     *      name="Create a family",
     *      description="Create a family",
     *      parent="pim_catalog_family"
     * )
     * @return array
     */
    public function createAction(Request $request)
    {
        $family   = new Family();
        $families = $this->getRepository('PimCatalogBundle:Family')->getIdToLabelOrderedByLabel();

        $form = $this->createForm('pim_family', $family);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager    = $this->container->get('pim_catalog.manager.product');
                $identifier = $manager->getIdentifierAttribute();
                $family->addAttribute($identifier);
                $this->persist($family);
                $this->addFlash('success', 'Family successfully created');

                $pendingManager = $this->container->get('pim_versioning.manager.pending');
                if ($pending = $pendingManager->getPendingVersion($family)) {
                    $pendingManager->createVersionAndAudit($pending);
                }

                return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
            }
        }

        return array(
            'form'     => $form->createView(),
            'families' => $families,
        );
    }

    /**
     * Edit a family
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_family_edit",
     *      name="Edit a family",
     *      description="Edit a family",
     *      parent="pim_catalog_family"
     * )
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $family   = $this->findOr404('PimCatalogBundle:Family', $id);
        $datagrid = $this->getDataAuditDatagrid($family, 'pim_catalog_family_edit', array('id' => $family->getId()));
        $datagridView = $datagrid->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        $families = $this->getRepository('PimCatalogBundle:Family')->getIdToLabelOrderedByLabel();
        $channels = $this->get('pim_catalog.manager.channel')->getChannels();
        $form = $this->createForm(
            'pim_family',
            $family,
            array(
                'channels'   => $channels,
                'attributes' => $family->getAttributes(),
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->flush();
                $this->addFlash('success', 'Family successfully updated.');

                $pendingManager = $this->container->get('pim_versioning.manager.pending');
                if ($pending = $pendingManager->getPendingVersion($family)) {
                    $pendingManager->createVersionAndAudit($pending);
                }

                return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $id));
            }
        }

        return array(
            'family'         => $family,
            'families'       => $families,
            'channels'       => $channels,
            'form'           => $form->createView(),
            'datagrid'       => $datagridView,
            'attributesForm' => $this->getAvailableProductAttributesForm(
                $family->getAttributes()->toArray()
            )->createView(),
        );
    }

    /**
     * Remove a family
     *
     * @param Family $entity
     * @Acl(
     *      id="pim_catalog_family_remove",
     *      name="Remove a family",
     *      description="Remove a family",
     *      parent="pim_catalog_family"
     * )
     * @return array
     */
    public function removeAction(Family $entity)
    {
        $this->remove($entity);

        $this->addFlash('success', 'Family successfully removed');

        return $this->redirectToRoute('pim_catalog_family_create');
    }

    /**
     * Add attributes to a family
     *
     * @param Request $request The request object
     * @param integer $id      The family id to which add attributes
     * @Acl(
     *      id="pim_catalog_family_add_attribute",
     *      name="Add attribute to a family",
     *      description="Add attribute to a family",
     *      parent="pim_catalog_family"
     * )
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction(Request $request, $id)
    {
        $family              = $this->findOr404('PimCatalogBundle:Family', $id);
        $availableAttributes = new AvailableProductAttributes();
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->bind($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->flush();

        return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
    }

    /**
     * Remove product attribute
     *
     * @param integer $familyId
     * @param integer $attributeId
     * @Acl(
     *      id="pim_catalog_family_remove_atribute",
     *      name="Remove attribute from a family",
     *      description="Remove attribute from a family",
     *      parent="pim_catalog_family"
     * )
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProductAttributeAction($familyId, $attributeId)
    {
        $family    = $this->findOr404('PimCatalogBundle:Family', $familyId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if (false === $family->hasAttribute($attribute)) {
            $this->addFlash('error', sprintf('Attribute "%s" is not attached to "%s" family', $attribute, $family));
        } elseif ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            $this->addFlash('error', 'Identifier attribute can not be removed from a family.');
        } elseif ($attribute === $family->getAttributeAsLabel()) {
            $this->addFlash('error', 'You cannot remove this attribute because it is used as label for the family.');
        } else {
            $family->removeAttribute($attribute);
            $this->flush();

            $this->addFlash('success', 'The family is successfully updated.');
        }

        return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
    }
}
