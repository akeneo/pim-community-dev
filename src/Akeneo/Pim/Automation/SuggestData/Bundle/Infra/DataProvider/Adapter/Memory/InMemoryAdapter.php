<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\Adapter\Memory;

use Pim\Component\Catalog\Model\ProductInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\DeserializeSuggestedDataCollection;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infra\DataProvider\SuggestedDataInterface;
use PimEnterprise\Component\SuggestData\PimAiClient\Api\Subscription\SubscriptionApiInterface;
use PimEnterprise\Component\SuggestData\Product\ProductCode;
use PimEnterprise\Component\SuggestData\Product\ProductCodeCollection;

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
    
    private $subscriptionApi;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     * @param SubscriptionApiInterface $subscriptionApi
     * @param array $config
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, SubscriptionApiInterface $subscriptionApi, array $config)
    {
        $this->deserializer = $deserializer;
        $this->subscriptionApi = $subscriptionApi;

        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function push(ProductInterface $product): SuggestedDataInterface
    {
        $identifier = $product->getIdentifier(); // TODO get with mapping

        $apiResponse = $this->subscriptionApi->subscribeProduct(new ProductCode('sku', $identifier));
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

        $apiResponse = $this->subscriptionApi->subscribeProducts($productCodes);

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
