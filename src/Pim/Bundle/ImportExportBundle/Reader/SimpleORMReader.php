<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * ORM Reader for simple entities without query join needed
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleORMReader extends ORMReader
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param ObjectManager $om        The object manager
     * @param string        $className The entity or document class name used
     */
    public function __construct(ObjectManager $om, $className)
    {
        $this->om              = $om;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    protected function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->om
                ->getRepository($this->className)
                ->createQueryBuilder('c')
                ->getQuery();
        }

        return $this->query;
    }
}
