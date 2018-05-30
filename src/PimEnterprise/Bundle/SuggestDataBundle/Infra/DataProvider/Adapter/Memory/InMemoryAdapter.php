<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\DeserializeSuggestedDataCollection;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataCollectionInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\SuggestedDataInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake\FakeHALProducts;

/**
 * In memory implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemoryAdapter implements DataProviderAdapterInterface
{
    /** @var array */
    private $pushedProducts;

    /** @var array */
    private $config;

    /** @var FakeHALProducts */
    private $fakeHALProducts;

    /** @var DeserializeSuggestedDataCollection */
    protected $deserializer;

    /**
     * @param DeserializeSuggestedDataCollection $deserializer
     * @param array                              $config
     */
    public function __construct(DeserializeSuggestedDataCollection $deserializer, array $config)
    {
        $this->deserializer = $deserializer;
        $this->fakeHALProducts = new FakeHALProducts();
        $this->pushedProducts = [];
        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function push(ProductInterface $product): SuggestedDataInterface
    {
        $identifier = $product->getIdentifier(); // TODO get with mapping
        if (!in_array($identifier, $this->pushedProducts)) {
            $this->pushedProducts[] = $identifier;
        }

        $hal = $this->fakeHALProducts->addProduct($identifier)->getFakeHAL();

        $collection = $this->deserializer->deserialize($hal);

        return $collection->current();
    }

    /**
     * {@inheritdoc}
     */
    public function bulkPush(array $products): SuggestedDataCollectionInterface
    {
        foreach ($products as $product) {
            $identifier = $product->getIdentifier(); // TODO get with mapping
            if (!in_array($identifier, $this->pushedProducts)) {
                $this->pushedProducts[] = $identifier;
            }
            $this->fakeHALProducts->addProduct($identifier);
        }

        $hal = $this->fakeHALProducts->getFakeHAL();

        return $this->deserializer->deserialize($hal);
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
