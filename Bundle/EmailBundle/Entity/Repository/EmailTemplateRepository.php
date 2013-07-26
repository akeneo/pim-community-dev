<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateRepository extends EntityRepository
{
    /**
     * Load templates by entity name
     *
     * @param $entityName
     * @return EmailTemplate[]
     */
    public function getTemplateByEntityName($entityName)
    {
        return $this->findBy(array('entityName' => $entityName));
    }

    /**
     * Return templates query builder filtered by entity name
     *
     * @param $entityName
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEntityTemplatesQueryBuilder($entityName)
    {
        return $this->createQueryBuilder('e')
            ->where('e.entityName = :entityName')
            ->orderBy('e.name', 'ASC')
            ->setParameter('entityName', $entityName);
    }
}
