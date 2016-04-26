<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Manager\VariantGroupAttributesResolver;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var RouterInterface */
    protected $router;

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

    /**
     * @param RouterInterface                $router
     * @param GroupManager                   $groupManager
     * @param HandlerInterface               $groupHandler
     * @param FormInterface                  $groupForm
     * @param GroupFactory                   $groupFactory
     * @param FormFactoryInterface           $formFactory
     * @param AttributeRepositoryInterface   $attributeRepository
     */
    public function __construct(
        RouterInterface $router,
        GroupManager $groupManager,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory,
        FormFactoryInterface $formFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->router              = $router;
        $this->formFactory         = $formFactory;
        $this->attributeRepository = $attributeRepository;
        $this->groupManager        = $groupManager;
        $this->groupFactory        = $groupFactory;
        $this->groupForm           = $groupForm;
        $this->groupHandler        = $groupHandler;
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
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('pim_enrich_variant_group_index'));
        }

        $group = $this->groupFactory->createGroup('VARIANT');

        if ($this->groupHandler->process($group)) {
            $request->getSession()->getFlashBag()->add('success', new Message('flash.variant group.created'));

            $url = $this->router->generate(
                'pim_enrich_variant_group_edit',
                ['code' => $group->getCode()]
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
     * @AclAncestor("pim_enrich_variant_group_edit")
     * @Template
     */
    public function editAction($code)
    {
        return [
            'code' => $code
        ];
    }
}
