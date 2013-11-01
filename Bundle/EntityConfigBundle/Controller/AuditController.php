<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;

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
     * @Template("OroDataAuditBundle:Audit/widget:history.html.twig")
     * @AclAncestor("oro_dataaudit_history")
     *
     * @param $entity
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function auditAction($entity, $id)
    {
        return [
            'gridName'    => 'audit-log-grid',
            'entityClass' => str_replace('_', '\\', $entity),
            'entityId'    => $id,
        ];
    }

    /**
     * @Route(
     *      "/audit_field/{entity}/{id}/{_format}",
     *      name="oro_entityconfig_audit_field",
     *      requirements={"entity"="[a-zA-Z0-9_]+", "id"="\d+"},
     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
     * )
     * @Template("OroDataAuditBundle:Audit/widget:history.html.twig")
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

        return [
            'gridName'    => 'auditfield-log-grid',
            'entityClass' => str_replace('_', '\\', $entity),
            'fieldName'   => $fieldName->getFieldName(),
            'entityId'    => $id,
        ];
    }
}
