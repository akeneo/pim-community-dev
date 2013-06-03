<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CompositeSearchFactory implements SearchFactoryInterface
{
    /**
     * @var SearchFactoryInterface[]
     */
    protected $factories;

    /**
     * @param SearchFactoryInterface[] $factories
     */
    public function __construct(array $factories = array())
    {
        $this->factories = $factories;
    }

    /**
     * @param string $type
     * @param SearchFactoryInterface $factory
     */
    public function addSearchFactory($type, SearchFactoryInterface $factory)
    {
        $this->factories[$type] = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options)
    {
        $type = $options['type'];
        if (!isset($this->factories[$type])) {
            throw new \RuntimeException(
                "Autocomplete factory for type \"$type\" is not registered"
            );
        }

        return $this->factories[$type]->create($options);
    }
}
