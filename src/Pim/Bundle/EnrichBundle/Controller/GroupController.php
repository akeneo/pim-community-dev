<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /** @var RemoverInterface */
    protected $groupRemover;

    /**
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param HandlerInterface             $groupHandler
     * @param FormInterface                $groupForm
     * @param GroupFactory                 $groupFactory
     * @param RemoverInterface             $groupRemover
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        GroupTypeRepositoryInterface $groupTypeRepository,
        HandlerInterface $groupHandler,
        FormInterface $groupForm,
        GroupFactory $groupFactory,
        RemoverInterface $groupRemover
    ) {
        $this->request = $request;
        $this->templating = $templating;
        $this->router = $router;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->groupHandler = $groupHandler;
        $this->groupForm = $groupForm;
        $this->groupFactory = $groupFactory;
        $this->groupRemover = $groupRemover;
    }

    /**
     * List groups
     *
     * @Template
     * @AclAncestor("pim_enrich_group_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $groupTypes = $this->groupTypeRepository->findTypeIds(false);

        return [
            'groupTypes' => $groupTypes
        ];
    }

    /**
     * Create a group
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_group_create")
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('pim_enrich_group_index'));
        }

        $group = $this->groupFactory->createGroup();

        if ($this->groupHandler->process($group)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.group.created'));

            $url = $this->router->generate(
                'pim_enrich_group_edit',
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
     * Edit a group
     *
     * TODO : find a way to use param converter with interfaces
     *
     * @param Group $group
     *
     * @Template
     * @AclAncestor("pim_enrich_group_edit")
     *
     * @return array
     */
    public function editAction(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.group.updated'));
        }

        return [
            'form'         => $this->groupForm->createView(),
            'currentGroup' => $group->getId()
        ];
    }

    /**
     * Remove a group
     *
     * TODO : find a way to use param converter with interfaces
     *
     * @param Group $group
     *
     * @AclAncestor("pim_enrich_group_remove")
     *
     * @return Response|RedirectResponse
     */
    public function removeAction(Request $request, Group $group)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $this->groupRemover->remove($group);

        if ($this->request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return new RedirectResponse($this->router->generate('pim_enrich_group_index'));
        }
    }

    /**
     * History of a group
     *
     * TODO : find a way to use param converter with interfaces
     *
     * @param Group $group
     *
     * @AclAncestor("pim_enrich_group_history")
     *
     * @return Response
     */
    public function historyAction(Group $group)
    {
        return $this->templating->renderResponse(
            'PimEnrichBundle:Group:Tab/_history.html.twig',
            [
                'group' => $group
            ]
        );
    }
}
