<?php

namespace Oro\Bundle\DataAuditBundle\Entity\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Gedmo\Tool\Wrapper\EntityWrapper;

class AuditRepository extends LogEntryRepository
{
    public function getLogEntriesQueryBuilder($entity)
    {
        $wrapped     = new EntityWrapper($entity, $this->_em);
        $objectClass = $wrapped->getMetadata()->name;
        $objectId    = $wrapped->getIdentifier();

        $qb = $this->createQueryBuilder('a')
            ->where('a.objectId = :objectId AND a.objectClass = :objectClass')
            ->orderBy('a.loggedAt', 'DESC')
            ->setParameters(compact('objectId', 'objectClass'));

        return $qb;
    }
}
