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
        if (!isset($options['properties'])) {
            throw new \RuntimeException('Option "properties" is required');
        }

        if (!isset($options['entity_class'])) {
            throw new \RuntimeException('Option "entity_class" is required');
        }

        $entityManagerName = isset($options['options']['entity_manager'])
            ? $options['options']['entity_manager'] : null;

        return new EntitySearchHandler(
            $this->managerRegistry->getManager($entityManagerName),
            $options['entity_class'],
            $options['properties']
        );
    }
}
