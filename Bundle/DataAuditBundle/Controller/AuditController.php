<?php

namespace Oro\Bundle\DataAuditBundle\Controller;

use Oro\Bundle\DataAuditBundle\Datagrid\AuditHistoryDatagridManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\DataAuditBundle\Entity\Audit;

/**
 * @Acl(
 *      id="oro_dataaudit",
 *      name="Data audit",
 *      description="Data audit"
 * )
 */
class AuditController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_dataaudit_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_dataaudit_index",
     *      name="View audit stream",
     *      description="View audit stream",
     *      parent="oro_dataaudit"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_dataaudit.datagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroDataAuditBundle:Audit:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * @Route(
     *      "/history/{entity}/{id}/{_format}",
     *      name="oro_dataaudit_history",
     *      requirements={"entity"="[a-zA-Z_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Template
     * @Acl(
     *      id="oro_dataaudit_history",
     *      name="View entity history",
     *      description="View entity history audit log",
     *      parent="oro_dataaudit"
     * )
     */
    public function historyAction($entity, $id)
    {
        /** @var $datagridManager AuditHistoryDatagridManager */
        $datagridManager = $this->get('oro_dataaudit.history.datagrid.manager');

        $datagridManager->entityClass   = str_replace('_', '\\', $entity);
        $datagridManager->entityClassId = $id;

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'entity' => $entity,
                'id'     => $id
            )
        );

        $datagridView = $datagridManager->getDatagrid()->createView();
        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView,
        );
    }
}
