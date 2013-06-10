<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class CompositeSearchFactory implements SearchFactoryInterface
{
    /**
     * @var SearchFactoryInterface[]
     */
    protected $factories = array();

    /**
     * @param SearchFactoryInterface[] $factories
     */
    public function __construct(array $factories = array())
    {
        foreach ($factories as $type => $factory) {
            $this->addSearchFactory($type, $factory);
        }
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
        if (!isset($options['type'])) {
            throw new \RuntimeException('Option "type" is required');
        }

        $type = $options['type'];

        if (!isset($this->factories[$type])) {
            throw new \RuntimeException(
                "Autocomplete search factory for type \"$type\" is not registered"
            );
        }

        return $this->factories[$type]->create($options);
    }
}
