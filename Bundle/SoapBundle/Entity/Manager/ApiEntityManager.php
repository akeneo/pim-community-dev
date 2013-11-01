<?php

namespace Oro\Bundle\SoapBundle\Entity\Manager;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class ApiEntityManager
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * Constructor
     *
     * @param string        $class Entity name
     * @param ObjectManager $om    Object manager
     */
    public function __construct($class, ObjectManager $om)
    {
        $this->om = $om;
        $this->metadata = $this->om->getClassMetadata($class);
        $this->class = $this->metadata->getName();
    }

    /**
     * Get entity metadata
     *
     * @return ClassMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Create new entity instance
     *
     * @return mixed
     */
    public function createEntity()
    {
        return new $this->class;
    }

    /**
     * Get entity by identifier.
     *
     * @param  mixed  $id
     * @return object
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param  object                    $entity
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getEntityId($entity)
    {
        $className = $this->class;
        if (!$entity instanceof $className) {
            throw new \InvalidArgumentException('Expected instance of ' . $this->class);
        }

        $idFields = $this->metadata->getIdentifierFieldNames();
        $idField = current($idFields);
        $entityIds = $this->metadata->getIdentifierValues($entity);

        return $entityIds[$idField];
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->getObjectManager()->getRepository($this->class);
    }

    /**
     * Retrieve object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Returns Paginator to paginate throw items.
     *
     * In case when limit and offset set to null QueryBuilder instance will be returned.
     *
     * @param  int          $limit
     * @param  int          $page
     * @param  null         $orderBy
     * @return \Traversable
     */
    public function getList($limit = 10, $page = 1, $orderBy = null)
    {
        $page = $page > 0 ? $page : 1;
        $orderBy = $orderBy ? $orderBy : $this->getDefaultOrderBy();

        return $this->getRepository()->findBy(array(), $this->getOrderBy($orderBy), $limit, $this->getOffset($page));
    }

    /**
     * Get order by
     *
     * @param $orderBy
     * @return array|null
     */
    protected function getOrderBy($orderBy)
    {
        return $orderBy ? $orderBy : $this->getDefaultOrderBy();
    }

    /**
     * Get offset by page
     *
     * @param  int|null $page
     * @return int
     */
    protected function getOffset($page)
    {
        if (!$page !== null) {
            $page = $page > 0 ? $page - 1 : 0;
        }

        return $page;
    }

    /**
     * Get default order by.
     *
     * @return array|null
     */
    protected function getDefaultOrderBy()
    {
        $ids = $this->metadata->getIdentifierFieldNames();
        $orderBy = $ids ? array() : null;
        foreach ($ids as $pk) {
            $orderBy[$pk] = 'ASC';
        }

        return $orderBy;
    }
}
