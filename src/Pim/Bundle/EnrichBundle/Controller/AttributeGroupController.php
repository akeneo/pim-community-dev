<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Handler\AttributeGroupHandler;
use Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
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
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var AttributeGroupHandler
     */
    protected $formHandler;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var AttributeGroupManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $attributeClass;

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
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param SecurityFacade           $securityFacade
     * @param AttributeGroupHandler    $formHandler
     * @param Form                     $form
     * @param AttributeGroupManager    $manager
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
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        SecurityFacade $securityFacade,
        AttributeGroupHandler $formHandler,
        Form $form,
        AttributeGroupManager $manager,
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
            $eventDispatcher,
            $doctrine
        );

        $this->securityFacade = $securityFacade;
        $this->formHandler    = $formHandler;
        $this->form           = $form;
        $this->manager        = $manager;
        $this->attributeClass = $attributeClass;
    }
    /**
     * Create attribute group
     *
     * @Template()
     * @AclAncestor("pim_enrich_attribute_group_index")
     * @return array
     */
    public function createAction()
    {
        if ($this->securityFacade->isGranted('pim_enrich_attribute_group_create')) {
            $group = new AttributeGroup();

            if ($this->formHandler->process($group)) {
                $this->addFlash('success', 'flash.attribute group.created');

                return $this->redirectToRoute('pim_enrich_attributegroup_edit', array('id' => $group->getId()));
            }

            $form = $this->form->createView();
            $attributesForm = $this->getAvailableAttributesForm($this->getGroupedAttributes())->createView();
        } else {
            $group = null;
            $form = null;
            $attributesForm = null;
        }

        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToLabelOrderedBySortOrder();

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $form,
            'attributesForm' => $attributesForm,
        );
    }

    /**
     * Edit attribute group
     *
     * @param AttributeGroup $group
     *
     * @Template
     * @AclAncestor("pim_enrich_attribute_group_edit")
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        $groups = $this->getRepository('PimCatalogBundle:AttributeGroup')->getIdToLabelOrderedBySortOrder();

        if ($this->formHandler->process($group)) {
            $this->addFlash('success', 'flash.attribute group.updated');

            return $this->redirectToRoute('pim_enrich_attributegroup_edit', array('id' => $group->getId()));
        }

        return array(
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->form->createView(),
            'attributesForm' => $this->getAvailableAttributesForm($this->getGroupedAttributes())->createView(),
        );
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_attribute_group_sort")
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_attributegroup_create');
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
            $this->getManagerForClass('PimCatalogBundle:AttributeGroup')->flush();

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
     * @throws \LogicException
     *
     * @AclAncestor("pim_enrich_attribute_group_remove")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, AttributeGroup $group)
    {
        if ($group === $this->getDefaultGroup()) {
            throw new \LogicException($this->translator->trans('flash.attribute group.not removed default'));
        }

        if (0 !== $group->getAttributes()->count()) {
            $this->addFlash('error', 'flash.attribute group.not removed attributes');
            throw new \LogicException($this->translator->trans('flash.attribute group.not removed attributes'));
        }

        $this->remove($group);

        if ($request->get('_redirectBack')) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_attributegroup_create');
        }
    }

    /**
     * Get the AvailbleAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = array(),
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            array('attributes' => $attributes)
        );
    }

    /**
     * Add attributes to a group
     *
     * @param Request $request The request object
     * @param integer $id      The group id to add attributes to
     *
     * @AclAncestor("pim_enrich_attribute_group_add_attribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction(Request $request, $id)
    {
        $group               = $this->findOr404('PimCatalogBundle:AttributeGroup', $id);
        $maxOrder            = $group->getMaxAttributeSortOrder();
        $availableAttributes = new AvailableAttributes();

        $attributesForm      = $this->getAvailableAttributesForm(
            $this->getGroupedAttributes(),
            $availableAttributes
        );

        $attributesForm->bind($request);
        foreach ($availableAttributes->getAttributes() as $attribute) {
            $attribute->setSortOrder(++$maxOrder);
            $group->addAttribute($attribute);
        }

        $this->getManagerForClass('PimCatalogBundle:AttributeGroup')->flush();

        $this->addFlash('success', 'flash.attribute group.attributes added');

        return $this->redirectToRoute('pim_enrich_attributegroup_edit', array('id' => $group->getId()));
    }

    /**
     * Remove an attribute
     *
     * @param integer $groupId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_enrich_attribute_group_remove_attribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAttributeAction($groupId, $attributeId)
    {
        $group     = $this->findOr404('PimCatalogBundle:AttributeGroup', $groupId);
        $attribute = $this->findOr404($this->attributeClass, $attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw $this->createNotFoundException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        if ($group === $this->getDefaultGroup()) {
            throw new \LogicException($this->translator->trans('flash.attribute group.not removed default attributes'));
        }

        $this->manager->removeAttribute($group, $attribute);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_attributegroup_edit', array('id' => $group->getId()));
        }
    }

    /**
     * Get attributes that belong to a group
     *
     * @return array
     */
    protected function getGroupedAttributes()
    {
        return $this->getRepository($this->attributeClass)->findAllInDefaultGroup();
    }

    /**
     * @return AttributeGroup
     */
    protected function getDefaultGroup()
    {
        return $this->getRepository('PimCatalogBundle:AttributeGroup')->findDefaultAttributeGroup();
    }
}
