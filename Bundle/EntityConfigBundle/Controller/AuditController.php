<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Datagrid\AuditDatagridManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\AuditFieldDatagridManager;

/**
 * EntityBundle controller.
 * @Route("/oro_entityconfig")
 * @Acl(
 *      id="oro_entityconfig",
 *      name="Entity config manipulation",
 *      description="Entity config manipulation"
 * )
 */
class AuditController extends Controller
{
    /**
     * @Route(
     *      "/audit/{entity}/{id}/{_format}",
     *      name="oro_entityconfig_audit",
     *      requirements={"entity"="[a-zA-Z_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Acl(
     *      id="oro_entityconfig_audit",
     *      name="View entity history",
     *      description="View entity history audit log",
     *      parent="oro_entityconfig"
     * )
     *
     * @param $entity
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function auditAction($entity, $id)
    {
        /** @var $datagridManager AuditDatagridManager */
        $datagridManager = $this->get('oro_entity_config.audit_datagrid.manager');

        $datagridManager->entityClass   = str_replace('_', '\\', $entity);
        $datagridManager->entityClassId = $id;

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'entity' => $entity,
                'id'     => $id
            )
        );

        $view = $datagridManager->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render('OroEntityConfigBundle:Audit:audit.html.twig', array('datagrid' => $view));
    }

    /**
     * @Route(
     *      "/audit_field/{entity}/{id}/{_format}",
     *      name="oro_entityconfig_audit_field",
     *      requirements={"entity"="[a-zA-Z_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Acl(
     *      id="oro_entityconfig_audit_field",
     *      name="View entity's field history",
     *      description="View entity's field history audit log",
     *      parent="oro_entityconfig"
     * )
     *
     * @param $entity
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function auditFieldAction($entity, $id)
    {
        /** @var FieldConfigModel $fieldName */
        $fieldName = $this->getDoctrine()
            ->getRepository(FieldConfigModel::ENTITY_NAME)
            ->findOneBy(array('id' => $id));

        /** @var $datagridManager AuditFieldDatagridManager */
        $datagridManager = $this->get('oro_entity_config.audit_field_datagrid.manager');

        $datagridManager->entityClass   = str_replace('_', '\\', $entity);
        $datagridManager->fieldName     = $fieldName->getFieldName();

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'entity' => $entity,
                'id'     => $id
            )
        );

        $view = $datagridManager->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render('OroEntityConfigBundle:Audit:audit.html.twig', array('datagrid' => $view));
    }
}
