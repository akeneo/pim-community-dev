<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;

/**
 * ORM Reader for simple entities without query join needed
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleORMReader extends ORMCursorReader
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
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
    protected function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository($this->className)
                ->createQueryBuilder('c')
                ->getQuery();
        }

        return $this->query;
    }
}
