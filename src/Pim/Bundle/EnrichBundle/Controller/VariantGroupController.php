<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Factory\GroupFactory;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController extends GroupController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var VariantGroupAttributesResolver */
    protected $groupAttrResolver;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var GroupManager */
    protected $groupManager;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var FormInterface */
    protected $groupForm;

    /** @var HandlerInterface */
    protected $groupHandler;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param Request                        $request
     * @param EngineInterface                $templating
     * @param RouterInterface                $router
     * @param GroupManager                   $groupManager
     * @param HandlerInterface               $groupHandler
     * @param FormInterface                  $groupForm
     * @param GroupFactory                   $groupFactory
     * @param FormFactoryInterface           $formFactory
     * @param RemoverInterface               $groupRemover
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param VariantGroupAttributesResolver $groupAttrResolver
     * @param RemoverInterface               $groupRemover
     * @param UserContext                    $userContext
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        GroupManager $groupManager,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory,
        FormFactoryInterface $formFactory,
        RemoverInterface $groupRemover,
        AttributeRepositoryInterface $attributeRepository,
        VariantGroupAttributesResolver $groupAttrResolver,
        RemoverInterface $groupRemover,
        UserContext $userContext
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $groupManager,
            $groupHandler,
            $groupForm,
            $groupFactory,
            $groupRemover
        );

        $this->formFactory         = $formFactory;
        $this->attributeRepository = $attributeRepository;
        $this->groupAttrResolver   = $groupAttrResolver;
        $this->groupManager        = $groupManager;
        $this->groupFactory        = $groupFactory;
        $this->groupForm           = $groupForm;
        $this->groupHandler        = $groupHandler;
        $this->userContext         = $userContext;
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     * @AclAncestor("pim_enrich_variant_group_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return [
            'groupTypes' => array_keys($this->groupManager->getTypeChoices(true))
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     * @AclAncestor("pim_enrich_variant_group_create")
     */
    public function createAction(Request $request)
    {
        $group = $this->groupFactory->createGroup('VARIANT');

        if ($this->groupHandler->process($group)) {
            $request->getSession()->getFlashBag()->add('success', new Message('flash.variant group.created'));

            $url = $this->router->generate(
                'pim_enrich_variant_group_edit',
                ['id' => $group->getId()]
            );
            $response = ['status' => 1, 'url' => $url];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->groupForm->createView()
        ];
    }

    /**
     * {@inheritdoc}
     *
     * TODO: find a way to use param converter with interfaces
     *
     * @AclAncestor("pim_enrich_variant_group_edit")
     * @Template
     */
    public function editAction(Request $request, Group $group)
    {
        if (!$group->getType()->isVariant()) {
            throw new NotFoundHttpException(sprintf('Variant group with id %d not found.', $group->getId()));
        }

        if ($this->groupHandler->process($group)) {
            $request->getSession()->getFlashBag()->add('success', new Message('flash.variant group.updated'));

            return new JsonResponse([
                'route'  => 'pim_enrich_variant_group_edit',
                'params' => ['id' => $group->getId(), 'dataLocale' => $this->userContext->getCurrentLocale()->getCode()]
            ]);
        }

        return [
            'form'           => $this->groupForm->createView(),
            'currentGroup'   => $group->getId(),
            'attributesForm' => $this->getAvailableAttributesForm($group)->createView(),
        ];
    }

    /**
     * Get the AvailableAttributes form
     *
     * @param GroupInterface $group
     *
     * @return FormInterface
     */
    protected function getAvailableAttributesForm(GroupInterface $group)
    {
        return $this->formFactory->create(
            'pim_available_attributes',
            new AvailableAttributes(),
            ['excluded_attributes' => $this->groupAttrResolver->getNonEligibleAttributes($group)]
        );
    }
}
