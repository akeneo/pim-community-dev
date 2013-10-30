<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateRepository extends EntityRepository
{
    /**
     * Gets a template by its name
     * This method can return null if the requested template does not exist
     *
     * @param string $templateName
     * @return EmailTemplate|null
     */
    public function findByName($templateName)
    {
        return $this->findOneBy(array('name' => $templateName));
    }

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
