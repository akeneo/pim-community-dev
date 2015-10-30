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
        $criteria = [
            'scopedEntity' => $entity,
            'recordId'     => $entityId,
        ];

        if (!is_null($section)) {
            $criteria['section'] = $section;
        }

        $scope = $this->findOneBy($criteria);
        if (!$scope) {
            return [];
        }

        $settings = [];
        foreach ($scope->getValues() as $value) {
            $settings[$value->getSection()][$value->getName()] = [
                'value'                  => $value->getValue(),
                'scope'                  => $scope->getEntity() ?: 'app',
                'use_parent_scope_value' => false
            ];
        }

        return $settings;
    }

    /**
     * @param $entityName
     * @param $scopeId
     * @return Config
     */
    public function getByEntity($entityName, $scopeId)
    {
        $config = $this->findOneBy(['scopedEntity' => $entityName, 'recordId' => $scopeId]);

        if (!$config) {
            $config = new Config();
            $config->setEntity($entityName)
                ->setRecordId($scopeId);
        }

        return $config;
    }
}
