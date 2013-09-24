<?php

namespace Oro\Bundle\EntityExtendBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityExtendBundle\Entity\EntityConfig;

class EntityConfigRepository extends EntityRepository
{
    public function getActiveConfig()
    {
        $result = $this->createQueryBuilder('ec')
            ->select('ec.config as config')
            ->where('ec.active = true')
            ->getQuery()->getSingleScalarResult();

        return unserialize($result);
    }

    public function createConfig($config, $active = true)
    {
        $entity = new EntityConfig($config, $active);

        $this->setActiveConfig($entity);
    }

    public function setActiveConfig(EntityConfig $config)
    {
        $this->createQueryBuilder('ec')
            ->update(EntityConfig::ENTITY_NAME, 'ec')
            ->set('ec.active', 0)
            ->getQuery()
            ->execute();

        $config->setActive(true);

        $this->_em->persist($config);
        $this->_em->flush($config);
    }
}
