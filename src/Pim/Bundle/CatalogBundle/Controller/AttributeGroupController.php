<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Form\Handler\AttributeGroupHandler;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;

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
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

    /**
     * @var AttributeGroupHandler
     */
    private $formHandler;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var string
     */
    private $attributeClass;

    /**
     * constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridHelperInterface  $datagridHelper
     * @param AttributeGroupHandler    $formHandler
     * @param Form                     $form
     * @param string                   $attributeClass
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridHelperInterface $datagridHelper,
        AttributeGroupHandler $formHandler,
        Form $form,
        $attributeClass
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridHelper = $datagridHelper;
        $this->formHandler    = $formHandler;
        $this->form           = $form;
        $this->attributeClass = $attributeClass;
    }
    /**
     * Create attribute group
     *
     * @Template()
     * @AclAncestor("pim_catalog_attribute_group_create")
     * @return array
     */
    public function createAction()
    {
        $group = new AttributeGroup();

        if ($this->formHandler->process($group)) {
            $this->addFlash('success', 'flash.attribute group.created');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToLabelOrderedBySortOrder();

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
     * @AclAncestor("pim_catalog_attribute_group_edit")
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToLabelOrderedBySortOrder();

        if ($this->formHandler->process($group)) {
            $this->addFlash('success', 'flash.attribute group.updated');

            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->form->createView(),
            'attributesForm' => $this->getAvailableProductAttributesForm($this->getGroupedAttributes())->createView(),
            'historyDatagrid' => $this->getHistoryGrid($group)->createView()
        );
    }

    /**
     * History of a group
     *
     * @param Request        $request
     * @param AttributeGroup $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function historyAction(Request $request, AttributeGroup $group)
    {
        $historyGridView = $this->getHistoryGrid($group)->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($historyGridView);
        }
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_catalog_attribute_group_sort")
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
     * @AclAncestor("pim_catalog_attribute_group_remove")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, AttributeGroup $group)
    {
        $this->getManager()->remove($group);
        $this->getManager()->flush();

        if ($request->get('_redirectBack')) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_attributegroup_create');
        }
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
            'pim_available_product_attributes',
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
     * @AclAncestor("pim_catalog_attribute_group_add_attribute")
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

        $this->addFlash('success', 'flash.attribute group.attributes added');

        return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
    }

    /**
     * Remove a product attribute
     *
     * @param integer $groupId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_catalog_attribute_group_remove_attribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeProductAttributeAction($groupId, $attributeId)
    {
        $group     = $this->findOr404('PimCatalogBundle:AttributeGroup', $groupId);
        $attribute = $this->findOr404($this->attributeClass, $attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        $group->removeAttribute($attribute);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_attributegroup_edit', array('id' => $group->getId()));
        }
    }

    /**
     * Get attributes that belong to a group
     *
     * @return array
     */
    protected function getGroupedAttributes()
    {
        return $this->getRepository($this->attributeClass)->findAllGrouped();
    }

    /**
     * @param AttributeGroup $group
     *
     * @return Datagrid
     */
    protected function getHistoryGrid(AttributeGroup $group)
    {
        $historyGrid = $this->datagridHelper->getDataAuditDatagrid(
            $group,
            'pim_catalog_attributegroup_history',
            array('id' => $group->getId())
        );

        return $historyGrid;
    }
}
