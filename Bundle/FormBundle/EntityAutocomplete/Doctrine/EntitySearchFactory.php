<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchFactoryInterface;

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
    public function create(array $options)
    {
        $entityManagerName = isset($options['options']['entity_manager'])
            ? $options['options']['entity_manager'] : null;

        if (!isset($options['options']['entity_name'])) {
            throw new \RuntimeException('Option "options.entity_name" is required');
        }

        if (!isset($options['properties'])) {
            throw new \RuntimeException('Option "properties" is required');
        }

        return new EntitySearchHandler(
            $this->managerRegistry->getManager($entityManagerName),
            $options['options']['entity_name'],
            $options['properties']
        );
    }
}
