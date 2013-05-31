<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchPropertyConfig;

class EntitySearchFactory implements SearchFactoryInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $config)
    {
        $entityManagerName = isset($config['entity_manager']) ? $config['entity_manager'] : null;

        if (!isset($config['entity_name'])) {
            throw new \RuntimeException('Config option "entity_name" is required');
        }

        if (!isset($config['properties'])) {
            throw new \RuntimeException('Config option "properties" is required');
        }

        return new EntitySearchHandler(
            $this->managerRegistry->getManager($entityManagerName),
            $config['entity_name'],
            $this->createSearchPropertiesConfig($config['properties'])
        );
    }

    /**
     * @param array $config
     * @return SearchPropertyConfig[]
     */
    protected function createSearchPropertiesConfig(array $config)
    {
        $result = array();
        foreach ($config['properties'] as $propertyConfig) {
            $result[] = SearchPropertyConfig::create($propertyConfig);
        }
        return $result;
    }
}
