<?php

namespace Oro\Bundle\EntityBundle\Audit;

use Metadata\MetadataFactory;

use Oro\Bundle\EntityBundle\Extend\ExtendManager;
use Oro\Bundle\EntityBundle\Metadata\AuditEntityMetadata;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class AuditManager
{
    /**
     * @var string
     */
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_REMOVE = 'remove';

    /**
     * @var string
     */
    const COMMIT_LEVEL_BASE     = 'commitLevel';
    const COMMIT_LEVEL_ADVANCED = 'commitLevel';

    /**
     * @var ExtendManager
     */
    protected $em;

    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var ConfigProvider
     */
    protected $auditConfigProvider;

    protected $pendingInsertEntities = array();

    public function __construct(ConfigProvider $auditConfigProvider, MetadataFactory $metadataFactory)
    {
        $this->auditConfigProvider = $auditConfigProvider;
        $this->metadataFactory     = $metadataFactory;
    }

    public function setExtendManager(ExtendManager $em)
    {
        $this->em = $em;
    }

    public function log()
    {
        $uow = $this->em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $updateEntity) {
            $this->createDiff(AuditManager::ACTION_CREATE, $updateEntity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $updateEntity) {
            $this->createDiff(AuditManager::ACTION_UPDATE, $updateEntity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $updateEntity) {
            $this->createDiff(AuditManager::ACTION_REMOVE, $updateEntity);
        }

    }

    public function postLog($entity)
    {
        $uow = $this->em->getUnitOfWork();

        $oid = spl_object_hash($entity);

        if ($this->pendingInsertEntities && array_key_exists($oid, $this->pendingInsertEntities)) {
            /*
            $logEntry     = $this->pendingInsertEntities[$oid];
            $logEntryMeta = $em->getClassMetadata(get_class($logEntry));

            $id = $this->getIdentifier($entity);
            $logEntryMeta->getReflectionProperty('objectId')->setValue($logEntry, $id);
            $uow->scheduleExtraUpdate($logEntry, array(
                'objectId' => array(null, $id)
            ));

            $uow->setOriginalEntityProperty(spl_object_hash($logEntry), 'objectId', $id);

            unset($this->pendingLogEntityInserts[$oid]);
            */
        }

    }

    protected function createDiff($action, $entity)
    {
        if ($this->isEntityAuditable(get_class($entity))) {
            $entityId = $this->getIdentifier($entity);

            if (!$entityId && $action === self::ACTION_CREATE) {
                $this->pendingInsertEntities[spl_object_hash($entity)] = $entity;

                return;
            }

            $entityMeta = $this->em->getClassMetadata(get_class($entity));

            foreach ($this->em->getUnitOfWork()->getEntityChangeSet($entity) as $field => $changes) {
                var_dump($field);
                switch (true) {
                    case $entityMeta->isSingleValuedAssociation($field):
                        //var_dump($this->getIdentifier($changes[0]), $this->getIdentifier($changes[1]));
                        break;
                    case $entityMeta->isCollectionValuedAssociation($field):
                        //var_dump($changes->first()->getName());
                        //var_dump(get_class_methods($changes));
                        break;
                    default:

                        var_dump($entity);
                        var_dump($changes);
                }
                if ($entityMeta->isSingleValuedAssociation($field)) {

                } else {
                }
            }

            die;
        }
    }

    /**
     * @param       $entity
     * @param  null $entityMeta
     * @return mixed
     */
    protected function getIdentifier($entity, $entityMeta = null)
    {
        $entityMeta      = $entityMeta ? $entityMeta : $this->em->getClassMetadata(get_class($entity));
        $identifierField = $entityMeta->getSingleIdentifierFieldName($entityMeta);

        return $entityMeta->getReflectionProperty($identifierField)->getValue($entity);
    }

    protected function isEntityAuditable($entityClassName)
    {
        if ($this->auditConfigProvider->hasConfig($entityClassName)
            && $this->auditConfigProvider->getConfig($entityClassName)->is('auditable')
        ) {
            return true;
        }

        /** @var AuditEntityMetadata $metadata */
        if ($metadata = $this->metadataFactory->getMetadataForClass($entityClassName)) {
            return $metadata->auditable;
        }

        return false;
    }

    /**
     * @param $entityClassName
     * @param $fieldName
     * @return bool|mixed
     */
    protected function isFieldAuditable($entityClassName, $fieldName)
    {
        if (!$this->isEntityAuditable($entityClassName)) {
            return false;
        }

        if ($this->auditConfigProvider->hasFieldConfig($entityClassName, $fieldName)
            && ($fieldConfig = $this->auditConfigProvider->getFieldConfig($entityClassName, $fieldName))
        ) {
            return $fieldConfig->get('auditable');
        }

        /** @var AuditEntityMetadata $metadata */
        if ($metadata = $this->metadataFactory->getMetadataForClass($entityClassName)
            && isset($metadata->propertyMetadata[$fieldName])
        ) {
            return $metadata->propertyMetadata[$fieldName]->commitLevel;
        }

        return false;
    }
}
