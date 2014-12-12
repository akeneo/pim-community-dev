<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader;

/**
 * Attribute option reader sorted by the sortOrder
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionReader extends Reader
{
    /** @var EntityManager */
    protected $em;

    /** @var string        */
    protected $className;

    /**
     * @param EntityManager $em        The entity manager
     * @param string        $className The entity class name used
     */
    public function __construct(EntityManager $em, $className)
    {
        $this->em        = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('ao')
                ->orderBy('ao.attribute')
                ->addOrderBy('ao.sortOrder')
                ->getQuery();
        }

        return $this->query;
    }
}
