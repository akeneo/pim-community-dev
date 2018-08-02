<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\Memory;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\DeserializeSuggestedDataCollection;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCode;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\ValueObject\ProductCodeCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * In memory implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemory implements DataProviderInterface
{
    /**
     * @const string A hard-coded token for acceptance tests.
     */
    private const PIM_AI_TOKEN = 'the-only-valid-token-for-acceptance';

    /** @var array */
    private $config;

    /** @var DeserializeSuggestedDataCollection */
    protected $deserializer;

    private $subscriptionApi;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     * @param SubscriptionApiInterface $subscriptionApi
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, SubscriptionApiInterface $subscriptionApi)
    {
        $this->deserializer = $deserializer;
        $this->subscriptionApi = $subscriptionApi;

        $config = ['url' => 'pim.ai.host', 'token' => 'my_personal_token'];
        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse
    {
        return new ProductSubscriptionResponse($request->getProduct(), uniqid(), []);
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

    public function authenticate(?string $token): bool
    {
        return static::PIM_AI_TOKEN === $token;
    }

    /**
     * @param array $config
     */
    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
