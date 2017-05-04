<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\EnrichBundle\Event\AttributeGroupEvents;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Manager\AttributeGroupManager;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AttributeGroup controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupController
{
    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var HandlerInterface */
    protected $formHandler;

    /** @var Form */
    protected $form;

    /** @var AttributeGroupManager */
    protected $manager;

    /** @var BulkSaverInterface */
    protected $attributeSaver;

    /** @var RemoverInterface */
    protected $attrGroupRemover;

    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepo;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepo;

    /**
     * @param Request                           $request
     * @param RouterInterface                   $router
     * @param FormFactoryInterface              $formFactory
     * @param TranslatorInterface               $translator
     * @param EventDispatcherInterface          $eventDispatcher
     * @param SecurityFacade                    $securityFacade
     * @param HandlerInterface                  $formHandler
     * @param Form                              $form
     * @param AttributeGroupManager             $manager
     * @param BulkSaverInterface                $attributeSaver
     * @param RemoverInterface                  $attrGroupRemover
     * @param AttributeGroupRepositoryInterface $attributeGroupRepo
     * @param AttributeRepositoryInterface      $attributeRepo
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        SecurityFacade $securityFacade,
        HandlerInterface $formHandler,
        Form $form,
        AttributeGroupManager $manager,
        BulkSaverInterface $attributeSaver,
        RemoverInterface $attrGroupRemover,
        AttributeGroupRepositoryInterface $attributeGroupRepo,
        AttributeRepositoryInterface $attributeRepo
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityFacade = $securityFacade;
        $this->formHandler = $formHandler;
        $this->form = $form;
        $this->manager = $manager;
        $this->attributeSaver = $attributeSaver;
        $this->attrGroupRemover = $attrGroupRemover;
        $this->attributeGroupRepo = $attributeGroupRepo;
        $this->attributeRepo = $attributeRepo;
    }

    /**
     * Attribute group index
     *
     * @Template
     * @AclAncestor("pim_enrich_attributegroup_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $groups = $this->attributeGroupRepo->getIdToLabelOrderedBySortOrder();

        return ['groups' => $groups];
    }

    /**
     * Create attribute group
     *
     * @Template()
     * @AclAncestor("pim_enrich_attributegroup_create")
     *
     * @return array
     */
    public function createAction()
    {
        if ($this->securityFacade->isGranted('pim_enrich_attributegroup_create')) {
            $group = new AttributeGroup();

            if ($this->formHandler->process($group)) {
                $this->eventDispatcher->dispatch(AttributeGroupEvents::POST_CREATE, new GenericEvent($group));
                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message('flash.attribute group.created'));

                return new JsonResponse(
                    ['route' => 'pim_enrich_attributegroup_edit', 'params' => ['id' => $group->getId()]]
                );
            }

            $form = $this->form->createView();
            $attributesForm = $this->getAvailableAttributesForm($this->getGroupedAttributes())->createView();
        } else {
            $group = null;
            $form = null;
            $attributesForm = null;
        }

        $groups = $this->attributeGroupRepo->getIdToLabelOrderedBySortOrder();

        return [
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $form,
            'attributesForm' => $attributesForm,
        ];
    }

    /**
     * Edit attribute group
     *
     * @param AttributeGroup $group
     *
     * @Template
     * @AclAncestor("pim_enrich_attributegroup_edit")
     *
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        $groups = $this->attributeGroupRepo->getIdToLabelOrderedBySortOrder();

        if ($this->formHandler->process($group)) {
            $this->request->getSession()->getFlashBag()
                ->add('success', new Message('flash.attribute group.updated'));

            return new JsonResponse(
                ['route' => 'pim_enrich_attributegroup_edit', 'params' => ['id' => $group->getId()]]
            );
        }

        return [
            'groups'         => $groups,
            'group'          => $group,
            'form'           => $this->form->createView(),
            'attributesForm' => $this->getAvailableAttributesForm($this->getGroupedAttributes())->createView(),
        ];
    }

    /**
     * Edit AttributeGroup sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_attributegroup_sort")
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        $data = $request->request->all();

        if (!empty($data)) {
            $groups = [];
            foreach ($data as $id => $sort) {
                $group = $this->attributeGroupRepo->find((int) $id);
                if ($group) {
                    $group->setSortOrder((int) $sort);
                    $groups[] = $group;
                }
            }
            $this->attributeSaver->saveAll($groups);

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
     * @throws DeleteException
     *
     * @AclAncestor("pim_enrich_attributegroup_remove")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, AttributeGroup $group)
    {
        if ($group === $this->getDefaultGroup()) {
            throw new DeleteException($this->translator->trans('flash.attribute group.not removed default'));
        }

        if (0 !== $group->getAttributes()->count()) {
            $this->request->getSession()->getFlashBag()
                ->add('error', new Message('flash.attribute group.not removed attributes'));

            throw new DeleteException($this->translator->trans('flash.attribute group.not removed attributes'));
        }

        $this->attrGroupRemover->remove($group);

        return new Response('', 204);
    }

    /**
     * Get the AvailableAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = [],
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            ['excluded_attributes' => $attributes]
        );
    }

    /**
     * Add attributes to a group
     *
     * @param Request $request The request object
     * @param int     $id      The group id to add attributes to
     *
     * @AclAncestor("pim_enrich_attributegroup_add_attribute")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction(Request $request, $id)
    {
        $group = $this->findAttributeGroupOr404($id);
        $availableAttributes = new AvailableAttributes();

        $attributesForm = $this->getAvailableAttributesForm(
            $this->getGroupedAttributes(),
            $availableAttributes
        );
        $attributesForm->submit($request);

        $this->manager->addAttributes($group, $availableAttributes->getAttributes());
        $this->request->getSession()->getFlashBag()
            ->add('success', new Message('flash.attribute group.attributes added'));

        return new JsonResponse(
            ['route' => 'pim_enrich_attributegroup_edit', 'params' => ['id' => $group->getId()]]
        );
    }

    /**
     * Remove an attribute
     *
     * @param int $groupId
     * @param int $attributeId
     *
     * @AclAncestor("pim_enrich_attributegroup_remove_attribute")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAttributeAction($groupId, $attributeId)
    {
        $group = $this->findAttributeGroupOr404($groupId);
        $attribute = $this->findAttributeOr404($attributeId);

        if (false === $group->hasAttribute($attribute)) {
            throw new NotFoundHttpException(
                sprintf('Attribute "%s" is not attached to "%s"', $attribute, $group)
            );
        }

        if ($group === $this->getDefaultGroup()) {
            throw new \LogicException($this->translator->trans('flash.attribute group.not removed default attributes'));
        }

        $this->manager->removeAttribute($group, $attribute);

        return new Response('', 204);
    }

    /**
     * Get attributes that belong to a group
     *
     * @return array
     */
    protected function getGroupedAttributes()
    {
        return $this->attributeRepo->findAllInDefaultGroup();
    }

    /**
     * @return AttributeGroup
     */
    protected function getDefaultGroup()
    {
        return $this->attributeGroupRepo->findDefaultAttributeGroup();
    }

    /**
     * @param int $id
     *
     * @return AttributeGroupInterface
     */
    protected function findAttributeGroupOr404($id)
    {
        $result = $this->attributeGroupRepo->find($id);

        if (null === $result) {
            throw new NotFoundHttpException('Attribute group not found');
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $result = $this->attributeRepo->find($id);

        if (!$result) {
            throw new NotFoundHttpException('Attribute not found');
        }

        return $result;
    }
}
