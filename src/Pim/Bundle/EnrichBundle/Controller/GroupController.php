<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Factory\GroupFactory;
use Pim\Bundle\CatalogBundle\Manager\GroupManager;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController extends AbstractController
{
    /** @staticvar integer The maximum number of group products to be displayed */
    const MAX_PRODUCTS = 5;

    /** @var GroupManager */
    protected $groupManager;

    /** @var HandlerInterface */
    protected $groupHandler;

    /** @var Form */
    protected $groupForm;

    /** @var GroupFactory */
    protected $groupFactory;

    /** @var RemoverInterface */
    protected $groupRemover;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param GroupManager             $groupManager
     * @param HandlerInterface         $groupHandler
     * @param Form                     $groupForm
     * @param GroupFactory             $groupFactory
     * @param RemoverInterface         $groupRemover
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        GroupManager $groupManager,
        HandlerInterface $groupHandler,
        Form $groupForm,
        GroupFactory $groupFactory,
        RemoverInterface $groupRemover
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher
        );

        $this->groupManager = $groupManager;
        $this->groupHandler = $groupHandler;
        $this->groupForm    = $groupForm;
        $this->groupFactory = $groupFactory;
        $this->groupRemover = $groupRemover;
    }

    /**
     * List groups
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_group_index")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return [
            'groupTypes' => array_keys($this->groupManager->getTypeChoices(false))
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
            return $this->redirectToRoute('pim_enrich_group_index');
        }

        $group = $this->groupFactory->createGroup();

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.created');

            $url = $this->generateUrl(
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
            $this->addFlash('success', 'flash.group.updated');
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
    public function removeAction(Group $group)
    {
        $this->groupRemover->remove($group);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_group_index');
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
        return $this->render(
            'PimEnrichBundle:Group:_history.html.twig',
            [
                'group' => $group
            ]
        );
    }
}
