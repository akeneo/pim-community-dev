<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAppQuery implements GetAppQueryInterface
{
    private WebMarketplaceApiInterface $webMarketplaceApi;

    public function __construct(WebMarketplaceApiInterface $webMarketplaceApi)
    {
        $this->webMarketplaceApi = $webMarketplaceApi;
    }

    public function execute(string $id): ?App
    {
        $data = $this->webMarketplaceApi->getApp($id);
        if (null === $data) {
            return null;
        }

        return App::fromWebMarketplaceValues($data);
    }
}
