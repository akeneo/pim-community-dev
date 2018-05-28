<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\Memory;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;
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

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->fakeHALProducts = new FakeHALProducts();
        $this->pushedProducts = [];
        $this->configure($config);
    }

    /**
     * {@inheritdoc}
     */
    public function push(ProductInterface $product): SuggestedData
    {
        $identifier = $product->getIdentifier(); // TODO get with mapping
        if (!in_array($identifier, $this->pushedProducts)) {
            $this->pushedProducts[] = $identifier;
        }

        $hal = $this->fakeHALProducts->addProduct($identifier)->getFakeHAL();

        return SuggestedData
    }

    /**
     * {@inheritdoc}
     */
    public function bulkPush(array $products): SuggestedDataCollection
    {
        foreach ($products as $product) {
            $identifier = $product->getIdentifier(); // TODO get with mapping
            if (!in_array($identifier, $this->pushedProducts)) {
                $this->pushedProducts[] = $identifier;
            }
            $this->fakeHALProducts->addProduct($identifier);
        }

        return $this->fakeHALProducts->getFakeHAL();
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
