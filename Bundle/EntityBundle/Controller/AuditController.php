<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\EntityBundle\Datagrid\AuditDatagridManager;

/**
 * EntityBundle controller.
 * @Route("/oro_entity")
 */
class AuditController extends Controller
{
    /**
     * @Route(
     *      "/audit/{entity}/{id}/{_format}",
     *      name="oro_entity_audit",
     *      requirements={"entity"="[a-zA-Z_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Acl(
     *      id="oro_entity_audit",
     *      name="View entity history",
     *      description="View entity history audit log",
     *      parent="oro_entity"
     * )
     *
     * @param $entity
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function auditAction($entity, $id)
    {
        /** @var $datagridManager AuditDatagridManager */
        $datagridManager = $this->get('oro_entity.audit_datagrid.manager');

        $datagridManager->entityClass   = str_replace('_', '\\', $entity);
        $datagridManager->entityClassId = $id;

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'entity' => $entity,
                'id'     => $id
            )
        );

        $datagrid = $datagridManager->getDatagrid();

        $view = 'json' == $this->getRequest()->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityBundle:Audit:audit.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }
}
