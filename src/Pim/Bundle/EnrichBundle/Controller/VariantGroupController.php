<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Factory\GroupFactory;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController extends GroupController
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param GroupManager             $groupManager
     * @param HandlerInterface         $groupHandler
     * @param Form                     $groupForm
     * @param GroupFactory             $groupFactory
     * @param AttributeRepository      $attributeRepository
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
        GroupManager $groupManager,
        HandlerInterface $groupHandler,
        Form $groupForm,
        GroupFactory $groupFactory,
        AttributeRepository $attributeRepository
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
            $groupManager,
            $groupHandler,
            $groupForm,
            $groupFactory
        );

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     * @Template
     * @AclAncestor("pim_enrich_variant_group_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'groupTypes' => array_keys($this->groupManager->getTypeChoices(true))
        );
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     * @AclAncestor("pim_enrich_variant_group_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_variant_group_index');
        }

        $groupType = $this->groupManager
            ->getGroupTypeRepository()
            ->findOneBy(array('code' => 'VARIANT'));
        $group = $this->groupFactory->createGroup($groupType);

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.created');

            $url = $this->generateUrl(
                'pim_enrich_variant_group_edit',
                array('id' => $group->getId())
            );
            $response = array('status' => 1, 'url' => $url);

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->groupForm->createView()
        );
    }

    /**
     * {@inheritdoc}
     *
     * TODO: find a way to use param converter with interfaces
     *
     * @AclAncestor("pim_enrich_variant_group_edit")
     * @Template
     */
    public function editAction(Group $group)
    {
        if (!$group->getType()->isVariant()) {
            throw new NotFoundHttpException(sprintf('Variant group with id %d not found.', $group->getId()));
        }

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.variant group.updated');
        }

        return array(
            'form'           => $this->groupForm->createView(),
            'currentGroup'   => $group->getId(),
            'attributesForm' => $this->getAvailableAttributesForm($group)->createView(),
        );
    }

    /**
     * Get the AvailbleAttributes form
     *
     * @param GroupInterface $group
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getAvailableAttributesForm(GroupInterface $group)
    {
        $attributes = $group->getAttributes()->toArray();

        $template = $group->getProductTemplate();
        if (null !== $template) {
            foreach (array_keys($template->getValuesData()) as $attributeCode) {
                $attributes[] = $this->attributeRepository->findOneByCode($attributeCode);
            }
        }

        $uniqueAttributes = $this->attributeRepository->findBy(['unique' => true]);
        foreach ($uniqueAttributes as $attribute) {
            if (!in_array($attribute, $attributes)) {
                $attributes[] = $attribute;
            }
        }

        return $this->createForm(
            'pim_available_attributes',
            new AvailableAttributes(),
            ['attributes' => $attributes]
        );
    }
}
