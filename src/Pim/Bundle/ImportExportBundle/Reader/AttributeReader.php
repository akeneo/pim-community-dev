<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Attribute reader
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeReader extends ORMCursorReader
{
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function read(StepExecution $stepExecution)
    {
        if (!$this->query) {
            $this->query = $this->em
                ->getRepository('PimCatalogBundle:ProductAttribute')
                ->createQueryBuilder('c')
                ->getQuery();
        }

        return parent::read();
    }
}
