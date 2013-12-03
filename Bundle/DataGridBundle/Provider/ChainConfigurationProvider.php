<?php

namespace Oro\Bundle\DataGridBundle\Provider;

class ChainConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var ConfigurationProviderInterface[]
     */
    protected $providers = [];

    /**
     * Registers the given provider in the chain
     *
     * @param ConfigurationProviderInterface $provider
     */
    public function addProvider(ConfigurationProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable($gridName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration($gridName)
    {
        $foundProvider = null;
        foreach ($this->providers as $provider) {
            if ($provider->isApplicable($gridName)) {
                $foundProvider = $provider;
                break;
            }
        }

        if ($foundProvider === null) {
            throw new \RuntimeException(sprintf('A configuration for "%s" datagrid was not found.', $gridName));
        }

        return $foundProvider->getConfiguration($gridName);
    }
}
