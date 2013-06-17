<?php

namespace Oro\Bundle\EntityExtendBundle\Extend;

use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityExtendBundle\Backend\BackendInterface;

class ExtendRepository implements ObjectRepository
{
    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var EntityConfig
     */
    protected $config;

    public function __construct(BackendInterface $backend, EntityConfig $config)
    {
        $this->backend = $backend;
        $this->config  = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->config->getClassName();
    }
}