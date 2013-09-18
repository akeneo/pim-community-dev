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
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Form\Handler\AttributeGroupHandler;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Form\Type\AvailableProductAttributesType;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;

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
class AttributeGroupController extends AbstractDoctrineController
{
    /**
     * @var GridRenderer
     */
    private $gridRenderer;

    /**
     * @var DatagridWorkerInterface
     */
    private $dataGridWorker;

    /**
     * @var AttributeGroupHandler
     */
    private $formHandler;

    /**
     * @var Form
     */
    private $form;

    /**
     * constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param RegistryInterface        $doctrine
     * @param GridRenderer             $gridRenderer
     * @param DatagridWorkerInterface  $dataGridWorker
     * @param AttributeGroupHandler    $formHandler
     * @param Form                     $form
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $dataGridWorker,
        AttributeGroupHandler $formHandler,
        Form $form
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->gridRenderer   = $gridRenderer;
        $this->dataGridWorker = $dataGridWorker;
        $this->formHandler    = $formHandler;
        $this->form           = $form;
    }
    /**
     * Create attribute group
     *
     * @Template()
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
        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToNameOrderedBySortOrder();

        if ($this->formHandler->process($group)) {
            $this->addFlash('success', 'Attribute group successfully created');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->form->createView(),
            'attributesForm' => $this->getAvailableProductAttributesForm($this->getGroupedAttributes())->createView(),
        );
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

        $datagrid = $this->dataGridWorker->getDataAuditDatagrid(
            $group,
            'pim_catalog_attributegroup_edit',
            array('id' => $group->getId())
        );
        $datagridView = $datagrid->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->gridRenderer->renderResultsJsonResponse($datagridView);
        }

        if ($this->formHandler->process($group)) {
            $this->addFlash('success', 'Attribute group successfully saved');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->form->createView(),
            'attributesForm' => $this->getAvailableProductAttributesForm($this->getGroupedAttributes())->createView(),
            'datagrid'       => $datagridView,
        );
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     *
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
            new AvailableProductAttributesType(),
            $availableAttributes ?: new AvailableProductAttributes(),
            array('attributes' => $attributes)
        );
    }

    /**
     * Add attributes to a group
     *
     * @param Request $request The request object
     * @param integer $id      The group id to add attributes to
     *
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
        $maxOrder            = $group->getMaxAttributeSortOrder();
        $availableAttributes = new AvailableProductAttributes();

        $attributesForm      = $this->getAvailableProductAttributesForm(
            $this->getGroupedAttributes(),
            $availableAttributes
        );

        $attributesForm->bind($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $attribute->setSortOrder(++$maxOrder);
            $group->addAttribute($attribute);
        }

        $this->getManager()->flush();

        $this->addFlash('success', 'Attribute successfully added to the group');

        return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
    }

    /**
     * Remove a product attribute
     *
     * @param integer $groupId
     * @param integer $attributeId
     *
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
        $this->getManager()->flush();

        $this->addFlash('success', 'Attribute successfully removed from the group');

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
