<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Group controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController
{
    const MAX_PRODUCTS = 5;

    /** @var Request */
    protected $request;

    /** @var EngineInterface */
    protected $templating;

    /** @var RouterInterface */
    protected $router;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var HandlerInterface */
    protected $groupHandler;

    /** @var FormInterface */
    protected $groupForm;

    /** @var GroupFactory */
    protected $groupFactory;

    /**
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param HandlerInterface             $groupHandler
     * @param FormInterface                $groupForm
     * @param GroupFactory                 $groupFactory
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        GroupTypeRepositoryInterface $groupTypeRepository,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory
    ) {
        $this->request = $request;
        $this->templating = $templating;
        $this->router = $router;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->groupHandler = $groupHandler;
        $this->groupForm = $groupForm;
        $this->groupFactory = $groupFactory;
    }

    /**
     * Create a group
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_group_create")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $group = $this->groupFactory->createGroup();

        if ($this->groupHandler->process($group)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.group.created'));

            $url = $this->router->generate(
                'pim_enrich_group_edit',
                ['code' => $group->getCode()]
            );
            $response = ['status' => 1, 'url' => $url];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->groupForm->createView()
        ];
    }
}
