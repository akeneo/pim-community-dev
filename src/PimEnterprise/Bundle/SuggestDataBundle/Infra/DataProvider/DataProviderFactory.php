<?php

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider;

use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory\InMemoryAdapter;

/**
 * Data provider factory
 * Create the right adapter depending of the data provider used
 * and configure it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class DataProviderFactory
{
    /**
     * @return DataProviderAdapterInterface
     */
    public function create()
    {
        $adapter = $this->initialize();

        return $adapter;
    }

    /**
     * Create and configure the data provider
     */
    private function initialize()
    {
        // TODO: Remove hardcoded configuration
        $config = ['url' => 'pim.ai.host', 'token' => 'my_personal_token'];
        
        return new InMemoryAdapter($config);
    }
}
