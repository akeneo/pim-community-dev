<?php

namespace Oro\Bundle\ConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * ConfigValueRepository
 *
 */
class ConfigValueRepository extends EntityRepository
{
    /**
     * Remove values by params
     *
     * @param integer $configId
     * @param $removed
     * @return array
     */
    public function removeValues($configId, $removed)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $this->getEntityManager()->beginTransaction();
        foreach ($removed as $item) {
            $builder->delete('OroConfigBundle:ConfigValue', 'cv')
                ->where('cv.config = :configId')
                ->andWhere('cv.name = :name')
                ->andWhere('cv.section = :section')
                ->setParameter('configId', $configId)
                ->setParameter('section', $item[0])
                ->setParameter('name', $item[1]);
            $builder->getQuery()->execute();
        }
        $this->getEntityManager()->commit();
    }
}
