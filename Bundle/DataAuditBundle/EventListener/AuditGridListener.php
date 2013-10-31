<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\ORM\EntityManager;

/**
 * Class AuditGridListener
 * Used to populate choices for objectClass column filter
 *
 * @package Oro\Bundle\DataAuditBundle
 */
class AuditGridListener
{
    /** @var EntityManager */
    protected $em;

    /** @var null|array */
    protected $objectClassChoices = null;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get distinct object classes
     *
     * @return array
     */
    public function getObjectClassOptions()
    {
        if (is_null($this->objectClassChoices)) {
            $options = array();

            $result = $this->em->createQueryBuilder()
                ->add('select', 'a.objectClass')
                ->add('from', 'Oro\Bundle\DataAuditBundle\Entity\Audit a')
                ->distinct('a.objectClass')
                ->getQuery()
                ->getArrayResult();

            foreach ((array) $result as $value) {
                $options[$value['objectClass']] = current(
                    array_reverse(
                        explode('\\', $value['objectClass'])
                    )
                );
            }

            $this->objectClassChoices = $options;
        }

        return $this->objectClassChoices;
    }
}
