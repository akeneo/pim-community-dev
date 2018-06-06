<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider;

use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory\InMemoryAdapter;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\EnrichmentApi;

/**
 * Data provider factory
 * Creates the right adapter depending of the data provider used
 * and configures it
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class DataProviderFactory
{
    /** @var DeserializeSuggestedDataCollection */
    protected $deserializer;
    
    private $enrichmentApi;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, EnrichmentApi $enrichmentApi)
    {
        $this->deserializer = $deserializer;
        $this->enrichmentApi = $enrichmentApi;
    }

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
        
        return new InMemoryAdapter($this->deserializer, $config, $this->enrichmentApi);
    }
}
