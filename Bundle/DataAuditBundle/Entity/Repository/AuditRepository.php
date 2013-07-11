<?php

namespace Oro\Bundle\DataAuditBundle\Entity\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

class AuditRepository extends LogEntryRepository
{
    public function getLogEntriesQueryBuilder($entity)
    {
        return $this->createQueryBuilder('a')
            ->where('a.objectId = :objectId AND a.objectClass = :objectClass')
            ->orderBy('a.loggedAt', 'DESC')
            ->setParameter('objectId', $entity->getId())
            ->setParameter('objectClass', get_class($entity))
        ;
    }
}

