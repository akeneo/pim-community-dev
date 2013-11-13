<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;

/**
 * Association reader
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationReader extends ORMCursorReader
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository('PimCatalogBundle:Association')
                ->createQueryBuilder('c')
                ->getQuery();
        }

        return parent::read();
    }
}
