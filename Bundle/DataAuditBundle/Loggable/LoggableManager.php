<?php
namespace Oro\Bundle\DataAuditBundle\Loggable;

use Symfony\Component\Routing\Exception\InvalidParameterException;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\DataAuditBundle\Metadata\PropertyMetadata;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Metadata\ClassMetadata;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO: This class should be refactored  (BAP-978)
 */
class LoggableManager
{
    /**
     * @var string
     */
    const ACTION_CREATE = 'create';

    /**
     * @var string
     */
    const ACTION_UPDATE = 'update';

    /**
     * @var string
     */
    const ACTION_REMOVE = 'remove';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $logEntityClass;

    /**
     * @var array
     */
    protected $pendingLogEntityInserts = array();

    /**
     * @var array
     */
    protected $pendingRelatedEntities = array();

    /**
     * @var array
     */
    protected $collectionLogData = array();

    /**
     * @var ConfigProvider
     */
    protected $auditConfigProvider;

    /**
     * @param                     $logEntityClass
     * @param ConfigProvider      $auditConfigProvider
     */
    public function __construct(
        $logEntityClass,
        ConfigProvider $auditConfigProvider
    ) {
        $this->auditConfigProvider = $auditConfigProvider;
        $this->logEntityClass      = $logEntityClass;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public function addConfig(ClassMetadata $metadata)
    {
        $this->configs[$metadata->name] = $metadata;
    }

    /**
     * @param $name
     * @return ClassMetadata
     * @throws InvalidParameterException
     */
    public function getConfig($name)
    {
        if (!isset($this->configs[$name])) {
            throw new InvalidParameterException(sprintf('invalid config name %s', $name));
        }

        return $this->configs[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasConfig($name)
    {
        return isset($this->configs[$name]);
    }

    /**
     * @param $username
     * @throws \InvalidArgumentException
     */
    public function setUsername($username)
    {
        if (is_string($username)) {
            $this->username = $username;
        } elseif (is_object($username) && method_exists($username, 'getUsername')) {
            $this->username = (string) $username->getUsername();
        } else {
            throw new \InvalidArgumentException("Username must be a string, or object should have method: getUsername");
        }
    }

    /**
     * @param EntityManager $em
     */
    public function handleLoggable(EntityManager $em)
    {
        $this->em = $em;
        $uow      = $em->getUnitOfWork();

        $collections = array_merge($uow->getScheduledCollectionUpdates(), $uow->getScheduledCollectionDeletions());
        foreach ($collections as $collection) {
            $this->calculateCollectionData($collection);
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->createLogEntity(self::ACTION_CREATE, $entity);
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->createLogEntity(self::ACTION_UPDATE, $entity);
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->createLogEntity(self::ACTION_REMOVE, $entity);
        }
    }

    /**
     * @param               $entity
     * @param EntityManager $em
     */
    public function handlePostPersist($entity, EntityManager $em)
    {
        $this->em = $em;
        $uow      = $em->getUnitOfWork();

        $oid = spl_object_hash($entity);

        if ($this->pendingLogEntityInserts && array_key_exists($oid, $this->pendingLogEntityInserts)) {
            $logEntry     = $this->pendingLogEntityInserts[$oid];
            $logEntryMeta = $em->getClassMetadata(ClassUtils::getClass($logEntry));

            $id = $this->getIdentifier($entity);
            $logEntryMeta->getReflectionProperty('objectId')->setValue($logEntry, $id);

            $uow->scheduleExtraUpdate(
                $logEntry,
                array(
                    'objectId' => array(null, $id)
                )
            );
            $uow->setOriginalEntityProperty(spl_object_hash($logEntry), 'objectId', $id);

            unset($this->pendingLogEntityInserts[$oid]);
        }

        if ($this->pendingRelatedEntities && array_key_exists($oid, $this->pendingRelatedEntities)) {
            $identifiers = $uow->getEntityIdentifier($entity);

            foreach ($this->pendingRelatedEntities[$oid] as $props) {
                $logEntry              = $props['log'];
                $oldData               = $data = $logEntry->getData();
                $data[$props['field']] = $identifiers;
                $logEntry->setData($data);

                $uow->scheduleExtraUpdate(
                    $logEntry,
                    array(
                        'data' => array($oldData, $data)
                    )
                );
                $uow->setOriginalEntityProperty(spl_object_hash($logEntry), 'objectId', $data);
            }

            unset($this->pendingRelatedEntities[$oid]);
        }
    }

    /**
     * @param PersistentCollection $collection
     */
    protected function calculateCollectionData(PersistentCollection $collection)
    {
        $ownerEntity = $collection->getOwner();

        if ($this->hasConfig(get_class($ownerEntity))) {
            $meta              = $this->getConfig(get_class($ownerEntity));
            $collectionMapping = $collection->getMapping();

            if (isset($meta->propertyMetadata[$collectionMapping['fieldName']])) {
                $method = $meta->propertyMetadata[$collectionMapping['fieldName']]->method;

                $newCollection = $collection->toArray();
                $oldCollection = $collection->getSnapshot();

                $oldData = array_reduce(
                    $oldCollection,
                    function ($result, $item) use ($method) {
                        return $result . ($result ? ', ' : '') . $item->{$method}();
                    }
                );

                $newData = array_reduce(
                    $newCollection,
                    function ($result, $item) use ($method) {
                        return $result . ($result ? ', ' : '') . $item->{$method}();
                    }
                );

                $this->collectionLogData[$collectionMapping['fieldName']] = array(
                    'old' => $oldData,
                    'new' => $newData,
                );
            }
        }
    }

    /**
     * @param string $action
     * @param mixed  $entity
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @throws \ReflectionException
     */
    protected function createLogEntity($action, $entity)
    {
        if (!$this->username) {
            return;
        }

        $this->checkAuditable($this->getEntityClassName($entity));

        /** @var User $user */
        $user = $this->em->getRepository('OroUserBundle:User')->findOneBy(array('username' => $this->username));

        if (!$user) {
            return;
        }

        $uow = $this->em->getUnitOfWork();

        if ($this->hasConfig($this->getEntityClassName($entity))) {
            $meta       = $this->getConfig($this->getEntityClassName($entity));
            $entityMeta = $this->em->getClassMetadata($this->getEntityClassName($entity));

            $logEntryMeta = $this->em->getClassMetadata($this->getLogEntityClass());
            /** @var Audit $logEntry */
            $logEntry = $logEntryMeta->newInstance();

            $logEntry->setAction($action);
            $logEntry->setObjectClass($meta->name);
            $logEntry->setLoggedAt();
            $logEntry->setUser($user);
            $logEntry->setObjectName(method_exists($entity, '__toString') ? $entity->__toString() : $meta->name);

            $entityId = $this->getIdentifier($entity);

            if (!$entityId && $action === self::ACTION_CREATE) {
                $this->pendingLogEntityInserts[spl_object_hash($entity)] = $logEntry;
            }

            $logEntry->setObjectId($entityId);

            $newValues = array();

            if ($action !== self::ACTION_REMOVE && count($meta->propertyMetadata)) {
                foreach ($uow->getEntityChangeSet($entity) as $field => $changes) {

                    if (!isset($meta->propertyMetadata[$field])) {
                        continue;
                    }

                    $old = $changes[0];
                    $new = $changes[1];

                    // fix issues with DateTime
                    if ($old == $new) {
                        continue;
                    }

                    if ($entityMeta->isSingleValuedAssociation($field) && $new) {
                        $oid   = spl_object_hash($new);
                        $value = $this->getIdentifier($new);

                        if (!is_array($value) && !$value) {
                            $this->pendingRelatedEntities[$oid][] = array(
                                'log'   => $logEntry,
                                'field' => $field
                            );
                        }

                        $method = $meta->propertyMetadata[$field]->method;
                        if ($old !== null) {
                            // check if an object has the required method to avoid a fatal error
                            if (!method_exists($old, $method)) {
                                throw new \ReflectionException(
                                    sprintf('Try to call to undefined method %s::%s', get_class($old), $method)
                                );
                            }
                            $old = $old->{$method}();
                        }
                        if ($new !== null) {
                            // check if an object has the required method to avoid a fatal error
                            if (!method_exists($new, $method)) {
                                throw new \ReflectionException(
                                    sprintf('Try to call to undefined method %s::%s', get_class($new), $method)
                                );
                            }
                            $new = $new->{$method}();
                        }
                    }

                    $newValues[$field] = array(
                        'old' => $old,
                        'new' => $new,
                    );
                }

                $newValues = array_merge($newValues, $this->collectionLogData);
                $logEntry->setData($newValues);
            }

            if ($action === self::ACTION_UPDATE
                && 0 === count($newValues)
            ) {
                return;
            }

            $version = 1;

            if ($action !== self::ACTION_CREATE) {
                $version = $this->getNewVersion($logEntryMeta, $entity);

                if (empty($version)) {
                    // was versioned later
                    $version = 1;
                }
            }

            $logEntry->setVersion($version);

            $this->em->persist($logEntry);
            $uow->computeChangeSet($logEntryMeta, $logEntry);
        }
    }

    /**
     * Get the LogEntry class
     *
     * @return string
     */
    protected function getLogEntityClass()
    {
        return $this->logEntityClass;
    }

    /**
     * @param $logEntityMeta
     * @param $entity
     * @return mixed
     */
    protected function getNewVersion($logEntityMeta, $entity)
    {
        $entityMeta = $this->em->getClassMetadata($this->getEntityClassName($entity));
        $entityId   = $this->getIdentifier($entity);

        $dql = "SELECT MAX(log.version) FROM {$logEntityMeta->name} log";
        $dql .= " WHERE log.objectId = :objectId";
        $dql .= " AND log.objectClass = :objectClass";

        $q = $this->em->createQuery($dql);
        $q->setParameters(
            array(
                'objectId'    => $entityId,
                'objectClass' => $entityMeta->name
            )
        );

        return $q->getSingleScalarResult() + 1;
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

    protected function checkAuditable($entityClassName)
    {
        if ($this->hasConfig($entityClassName)) {
            return;
        }

        if ($this->auditConfigProvider->hasConfig($entityClassName)
            && $this->auditConfigProvider->getConfig($entityClassName)->is('auditable')
        ) {
            $reflection    = new \ReflectionClass($entityClassName);
            $classMetadata = new ClassMetadata($reflection->getName());

            foreach ($reflection->getProperties() as $reflectionProperty) {
                $fieldName = $reflectionProperty->getName();
                if (strpos($fieldName, 'field_') === 0) {
                    $fieldName = str_replace('field_', '', $fieldName);
                }

                if ($this->auditConfigProvider->hasConfig($entityClassName, $fieldName)
                    && ($fieldConfig = $this->auditConfigProvider->getConfig($entityClassName, $fieldName))
                    && $fieldConfig->is('auditable')
                ) {
                    $propertyMetadata         = new PropertyMetadata($entityClassName, $reflectionProperty->getName());
                    $propertyMetadata->method = '__toString';

                    $classMetadata->addPropertyMetadata($propertyMetadata);
                }
            }

            if (count($classMetadata->propertyMetadata)) {
                $this->addConfig($classMetadata);
            }
        }
    }

    /**
     * @param $entity
     * @return string
     */
    private function getEntityClassName($entity)
    {
        if (is_object($entity)) {
            return get_class($entity);
        }

        return $entity;
    }
}
