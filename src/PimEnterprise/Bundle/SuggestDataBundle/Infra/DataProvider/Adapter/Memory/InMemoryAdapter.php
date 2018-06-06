<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DeserializeSuggestedDataCollection;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataCollectionInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataInterface;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\Api\EnrichmentApi;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCode;
use PimEnterprise\Bundle\SuggestDataBundle\PimAiClient\ValueObjects\Subscription\ProductCodeCollection;

/**
 * In memory implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemoryAdapter implements DataProviderAdapterInterface
{
    /** @var array */
    private $config;

    /** @var DeserializeSuggestedDataCollection */
    protected $deserializer;
    
    private $enrichmentApi;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     * @param array                              $config
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, array $config, EnrichmentApi $enrichmentApi)
    {
        $this->deserializer = $deserializer;
        $this->enrichmentApi = $enrichmentApi;
        
        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function push(ProductInterface $product): SuggestedDataInterface
    {
        $identifier = $product->getIdentifier(); // TODO get with mapping

        $apiResponse = $this->enrichmentApi->subscribeProduct(new ProductCode('sku', $identifier));
        $collection = $this->deserializer->deserialize($apiResponse->content());

        return $collection->current();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkPush(array $products): SuggestedDataCollectionInterface
    {
        $productCodes = new ProductCodeCollection();
        foreach ($products as $product) {
            $identifier = $product->getIdentifier(); // TODO get with mapping
            $productCodes->add(new ProductCode('sku', $identifier));
        }

        $apiResponse = $this->enrichmentApi->subscribeProducts($productCodes);

        return $this->deserializer->deserialize($apiResponse->content());
    }

    public function pull(ProductInterface $product)
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    public function bulkPull(array $products)
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    public function authenticate()
    {
        throw new \Exception(
            sprintf('"%s" is not yet implemented'),
            __METHOD__
        );
    }

    /**
     * @param array $config
     */
    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
