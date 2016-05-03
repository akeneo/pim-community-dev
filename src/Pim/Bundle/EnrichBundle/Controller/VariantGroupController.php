<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Manager\VariantGroupAttributesResolver;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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

    /**
     * @param Request                        $request
     * @param EngineInterface                $templating
     * @param RouterInterface                $router
     * @param GroupTypeRepositoryInterface   $groupTypeRepository
     * @param HandlerInterface               $groupHandler
     * @param FormInterface                  $groupForm
     * @param GroupFactory                   $groupFactory
     * @param RemoverInterface               $groupRemover
     * @param FormFactoryInterface           $formFactory
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param VariantGroupAttributesResolver $groupAttrResolver
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        GroupTypeRepositoryInterface $groupTypeRepository,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory,
        RemoverInterface $groupRemover,
        FormFactoryInterface $formFactory,
        AttributeRepositoryInterface $attributeRepository,
        VariantGroupAttributesResolver $groupAttrResolver
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $groupTypeRepository,
            $groupHandler,
            $groupForm,
            $groupFactory,
            $groupRemover
        );

        $this->formFactory         = $formFactory;
        $this->attributeRepository = $attributeRepository;
        $this->groupAttrResolver   = $groupAttrResolver;
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
        $groupTypes = $this->groupTypeRepository->findTypeIds(true);

        return [
            'groupTypes' => $groupTypes
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
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('pim_enrich_variant_group_index'));
        }

        $group = $this->groupFactory->createGroup('VARIANT');

        if ($this->groupHandler->process($group)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.variant group.created'));

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
    public function editAction(Group $group)
    {
        if (!$group->getType()->isVariant()) {
            throw new NotFoundHttpException(sprintf('Variant group with id %d not found.', $group->getId()));
        }

        if ($this->groupHandler->process($group)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.variant group.updated'));
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
