<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\PimAI;

use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider\Adapter\DataProviderAdapterInterface;

/**
 * Pim.ai implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimAIAdapter implements DataProviderAdapterInterface
{
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    public function push(ProductInterface $product)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function bulkPush(array $products)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function pull(ProductInterface $product)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function bulkPull(array $products)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function authenticate()
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function configure(array $config)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }
}
