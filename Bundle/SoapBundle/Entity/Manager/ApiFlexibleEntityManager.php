<?php

namespace Oro\Bundle\SoapBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class ApiFlexibleEntityManager extends ApiEntityManager
{
    /**
     * @var FlexibleManager
     */
    protected $fm;

    /**
     * Constructor
     *
     * @param string          $class Entity name
     * @param ObjectManager   $om    Object manager
     * @param FlexibleManager $fm    Proxy for methods of flexible manager
     */
    public function __construct($class, ObjectManager $om, FlexibleManager $fm)
    {
        parent::__construct($class, $om);
        $this->fm = $fm;
    }

    /**
     * {@inheritDoc}
     */
    public function createEntity()
    {
        return $this->getFlexibleManager()->createFlexible();
    }

    /**
     * @return FlexibleManager
     */
    public function getFlexibleManager()
    {
        return $this->fm;
    }

    /**
     * {@inheritDoc}
     */
    public function getList($limit = 10, $page = 1, $orderBy = null)
    {
        /** @var FlexibleEntityRepository $repository */
        $repository = $this->getFlexibleManager()->getFlexibleRepository();

        return $repository->findByWithAttributesQB(array(), null, $this->getOrderBy($orderBy), $limit, $this->getOffset($page));
    }
}
