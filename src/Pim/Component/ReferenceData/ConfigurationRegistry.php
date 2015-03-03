<?php

namespace Pim\Component\ReferenceData;

use Pim\Component\ReferenceData\Model\Configuration;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationRegistry implements ConfigurationRegistryInterface
{
    protected static $configurations = [];

    public function register(ConfigurationInterface $configuration, $name)
    {
        $configuration->setName($name);
        self::$configurations[$name] = $configuration;

        return $this;
    }

    public function registerRaw(array $rawConfiguration, $name)
    {
        $this->checkRawConfiguration($rawConfiguration);

        $configuration = new Configuration();
        $configuration->setType($rawConfiguration['type']);
        $configuration->setClass($rawConfiguration['class']);

        return $this->register($configuration, $name);
    }

    public function get($name)
    {
        return self::$configurations[$name];
    }

    public function all()
    {
        return self::$configurations;
    }

    public function unregister($type)
    {
        unset(self::$configurations[$type]);

        return $this;
    }

    protected function checkRawConfiguration(array $configuration)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['class', 'type']);
        $resolver->setAllowedTypes(['class' => 'string', 'type' => 'string' ]);

        $resolver->resolve($configuration);
    }
}
