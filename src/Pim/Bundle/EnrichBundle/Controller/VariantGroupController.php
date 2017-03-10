<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController
{
    /** @var RouterInterface */
    protected $router;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var FormInterface */
    protected $groupForm;

    /** @var HandlerInterface */
    protected $groupHandler;

    /** @var ProductTemplateBuilderInterface */
    protected $productTemplateBuilder;

    /**
     * @param RouterInterface                 $router
     * @param GroupTypeRepositoryInterface    $groupTypeRepository
     * @param HandlerInterface                $groupHandler
     * @param FormInterface                   $groupForm
     * @param GroupFactory                    $groupFactory
     * @param ProductTemplateBuilderInterface $productTemplateBuilder
     */
    public function __construct(
        RouterInterface $router,
        GroupTypeRepositoryInterface $groupTypeRepository,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory,
        ProductTemplateBuilderInterface $productTemplateBuilder
    ) {
        $this->router = $router;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->groupFactory = $groupFactory;
        $this->groupForm = $groupForm;
        $this->groupHandler = $groupHandler;
        $this->productTemplateBuilder = $productTemplateBuilder;
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
            'groupTypes' => $this->groupTypeRepository->findTypeIds(true)
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
        $group->setProductTemplate($this->productTemplateBuilder->createProductTemplate());

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
