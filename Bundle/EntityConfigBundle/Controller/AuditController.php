<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Datagrid\AuditDatagridManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\AuditFieldDatagridManager;

/**
 * EntityBundle controller.
 * @Route("/entity/config")
 */
class AuditController extends Controller
{
    /**
     * @Route(
     *      "/audit/{entity}/{id}/{_format}",
     *      name="oro_entityconfig_audit",
     *      requirements={"entity"="[a-zA-Z0-9_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_dataaudit_history")
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

        $datagridView = $datagridManager->getDatagrid()->createView();
        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView,
        );
    }

    /**
     * @Route(
     *      "/audit_field/{entity}/{id}/{_format}",
     *      name="oro_entityconfig_audit_field",
     *      requirements={"entity"="[a-zA-Z0-9_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Template("OroEntityConfigBundle:Audit:audit.html.twig")
     * @AclAncestor("oro_dataaudit_history")
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

        $datagridView = $datagridManager->getDatagrid()->createView();
        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView,
        );
    }
}
