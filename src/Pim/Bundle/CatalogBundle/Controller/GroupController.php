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
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
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
     * @var DatagridHelperInterface
     */
    protected $datagridHelper;

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
     * @param DatagridHelperInterface  $datagridHelper
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
        DatagridHelperInterface $datagridHelper,
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

        $this->datagridHelper = $datagridHelper;
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
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $datagrid = $this->datagridHelper->getDatagrid('group', $queryBuilder);

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
            return $this->redirectToRoute('pim_catalog_group_index');
        }

        $group = new Group();

        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.created');

            $url = $this->generateUrl(
                'pim_catalog_group_edit',
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
        if ($this->groupHandler->process($group)) {
            $this->addFlash('success', 'flash.group.updated');
        }

        $datagridManager = $this->datagridHelper->getDatagridManager('group_product');
        $datagridManager->setGroup($group);
        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
        }

        return array(
            'form'            => $this->groupForm->createView(),
            'datagrid'        => $datagridView,
            'historyDatagrid' => $this->getHistoryGrid($group)->createView()
        );
    }

    /**
     * History of a group
     *
     * @param Request $request
     * @param Group   $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function historyAction(Request $request, Group $group)
    {
        $historyGridView = $this->getHistoryGrid($group)->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($historyGridView);
        }
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
     * @param Group $group
     *
     * @return Datagrid
     */
    protected function getHistoryGrid(Group $group)
    {
        $historyGrid = $this->datagridHelper->getDataAuditDatagrid(
            $group,
            'pim_catalog_group_history',
            array('id' => $group->getId())
        );

        return $historyGrid;
    }
}
