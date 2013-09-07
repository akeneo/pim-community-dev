<?php

namespace Oro\Bundle\ConfigBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ConfigBundle\Entity\Config;

/**
 * Class ConfigRepository
 * @package Oro\Bundle\ConfigBundle
 */
class ConfigRepository extends EntityRepository
{
    /**
     * @param string $entity
     * @param string $entityId
     * @param string $section
     * @return array
     */
    public function loadSettings($entity, $entityId, $section)
    {
        $criteria = array(
            'scopedEntity' => $entity,
            'recordId'     => $entityId,
        );

        if (!is_null($section)) {
            $criteria['section'] = $section;
        }

        $scope = $this->findOneBy($criteria);
        if (!$scope) {
            return array();
        }

        $settings = array();
        foreach ($scope->getValues() as $value) {
            $settings[$value->getSection()][$value->getName()] = array(
                'value' => $value->getValue(),
                'scope' => $scope->getEntity() ?: 'app',
                'use_parent_scope_value' => false
            );
        }

        return $settings;
    }

    /**
     * @param null|object $entity
     * @return Config
     */
    public function getByEntity($entity)
    {
        $scopedId = $entity ? $entity->getId() : null;
        $config = $this->findOneBy(array('scopedEntity' => $entity, 'recordId' => $scopedId));

        if (!$config) {
            $config = new Config();
            $config->setEntity($entity)
                ->setRecordId($scopedId);
        }

        return $config;
    }
}