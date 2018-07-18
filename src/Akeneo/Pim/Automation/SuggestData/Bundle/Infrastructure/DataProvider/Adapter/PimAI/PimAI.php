<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\PimAI;

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\SuggestedDataInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Pim.ai implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimAI implements DataProviderInterface
{
    private $authenticationApi;

    public function __construct(AuthenticationApiInterface $authenticationApi)
    {
        $this->authenticationApi = $authenticationApi;
    }

    public function push(ProductInterface $product): SuggestedDataInterface
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }

    public function bulkPush(array $products): SuggestedDataCollectionInterface
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

    public function authenticate(?string $token): bool
    {
        return $this->authenticationApi->authenticate($token);
    }

    public function configure(array $config)
    {
        throw new \Exception(
            sprintf('"%s is not yet implemented'),
            __METHOD__
        );
    }
}
