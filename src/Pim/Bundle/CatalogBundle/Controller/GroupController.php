<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Form\Handler\GroupHandler;

/**
 * Group controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    protected $datagridWorker;

    /**
     * @var GroupHandler
     */
    protected $groupHandler;

    /**
     * @var Form
     */
    protected $groupForm;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridWorkerInterface  $datagridWorker
     * @param GroupHandler             $groupHandler
     * @param Form                     $groupForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $datagridWorker,
        GroupHandler $groupHandler,
        Form $groupForm
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridWorker = $datagridWorker;
        $this->groupHandler   = $groupHandler;
        $this->groupForm      = $groupForm;
    }

    /**
     * List groups
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_group_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->getGroupDatagrid();

        $view = ('json' === $request->getRequestFormat())
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'PimCatalogBundle:Group:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create a group
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_group_create")
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToIndex();
        }

        $group = $this->createGroup();

        if ($this->groupHandler->process($group)) {
            $this->successCreate($group);
        }

        return array(
            'form' => $this->groupForm->createView()
        );
    }

    /**
     * Edit a group
     *
     * @param Group $group
     *
     * @Template
     * @AclAncestor("pim_catalog_group_edit")
     * @return array
     */
    public function editAction(Group $group)
    {
        $this->processGroupHandler($group);

        $datagridManager = $this->datagridWorker->getDatagridManager('group_product');
        $datagridManager->setGroup($group);
        $datagridView = $datagridManager->getDatagrid()->createView();

        $historyDatagrid = $this->datagridWorker->getDataAuditDatagrid(
            $group,
            $this->getEditRoute(),
            array(
                'id' => $group->getId()
            )
        );
        $historyDatagridView = $historyDatagrid->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->render(
                'OroGridBundle:Datagrid:list.json.php',
                array('datagrid' => $datagridView)
            );
        }

        return array(
            'form'            => $this->groupForm->createView(),
            'datagrid'        => $datagridView,
            'historyDatagrid' => $historyDatagridView
        );
    }

    /**
     * Remove a group
     * @param Group $group
     *
     * @AclAncestor("pim_catalog_group_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Group $group)
    {
        $this->getManager()->remove($group);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_group_index');
        }
    }

    /**
     * Get the group datagrid
     *
     * @return \Oro\Bundle\GridBundle\Datagrid\Datagrid
     */
    protected function getGroupDatagrid()
    {
        $queryBuilder = $this->getManager()->createQueryBuilder();

        return $this->datagridWorker->getDatagrid('group', $queryBuilder);
    }

    /**
     * Redirect to the index page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndex()
    {
        return $this->redirectToRoute('pim_catalog_group_index');
    }

    /**
     * Create an empty group entity
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Group
     */
    protected function createGroup()
    {
        return new Group();
    }

    /**
     * Post process after successfully create a group
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function successCreate(Group $group)
    {
        $this->addFlash('success', 'flash.group.created');

        $url = $this->generateUrl($this->getEditRoute(), array('id' => $group->getId()));
        $response = array('status' => 1, 'url' => $url);

        return new Response(json_encode($response));
    }

    /**
     * Get edit route
     *
     * @return string
     */
    protected function getEditRoute()
    {
        return 'pim_catalog_group_edit';
    }

    /**
     * Process handler for group entity
     *
     * @param Group $group
     */
    protected function processGroupHandler(Group $group)
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.updated');
        }
    }
}
