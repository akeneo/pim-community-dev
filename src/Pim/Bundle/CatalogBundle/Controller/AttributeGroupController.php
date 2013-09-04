<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Form\Handler\AttributeGroupHandler;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Form\Type\AvailableProductAttributesType;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * AttributeGroup controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupController extends AbstractDoctrineController
{
    /**
     * @var AttributeGroupHandler
     */
    private $attributeGroupHandler;

    /**
     * @var Form
     */
    private $attributeGroupForm;

    /**
     * constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param RegistryInterface        $doctrine
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param AttributeGroupHandler    $attributeGroupHandler
     * @param Form                     $attributeGroupForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        AttributeGroupHandler $attributeGroupHandler,
        Form $attributeGroupForm
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->attributeGroupHandler = $attributeGroupHandler;
        $this->attributeGroupForm = $attributeGroupForm;
    }
    /**
     * Create attribute group
     *
     * @Template("PimCatalogBundle:AttributeGroup:edit.html.twig")
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
        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToNameOrderedBySortOrder();

        if ($this->attributeGroupHandler->process($group)) {
            $this->addFlash('success', 'Attribute group successfully saved');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->attributeGroupForm->createView(),
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
            return $this->redirectToRoute('pim_catalog_attributegroup_create');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $group = $this->getRepository('PimCatalogBundle:AttributeGroup')->find((int) $id);
                if ($group) {
                    $group->setSortOrder((int) $sort);
                    $this->getManager()->persist($group);
                }
            }
            $this->getManager()->flush();

            return new Response(1);
        }

        return new Response(0);
    }

    /**
     * Remove attribute group
     *
     * @param Request        $request
     * @param AttributeGroup $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, AttributeGroup $group)
    {
        $this->getManager()->remove($group);
        $this->getManager()->flush();

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
     * Get the AvailbleProductAttributes form
     *
     * @param array                      $attributes          The product attributes
     * @param AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Form
     */
    protected function getAvailableProductAttributesForm(
        array $attributes = array(),
        AvailableProductAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            new AvailableProductAttributesType,
            $availableAttributes ?: new AvailableProductAttributes,
            array('attributes' => $attributes)
        );
    }

    /**
     * Add attributes to a group
     *
     * @param Request $request The request object
     * @param integer $id      The group id to add attributes to
     *
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

        $this->getManager()->flush();

        return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
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
        $group     = $this->findOr404('PimCatalogBundle:AttributeGroup', $groupId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        $group->removeAttribute($attribute);
        $this->getManager()->flush();

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
