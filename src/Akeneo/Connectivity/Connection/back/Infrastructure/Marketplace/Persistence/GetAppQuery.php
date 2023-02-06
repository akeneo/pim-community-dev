<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAppQuery implements GetAppQueryInterface
{
    public function __construct(
        private WebMarketplaceApiInterface $webMarketplaceApi,
        private FeatureFlag $appDeveloperModeFeatureFlag,
        private GetCustomAppQuery $getTestAppQuery,
    ) {
    }

    public function execute(string $id): ?App
    {
        if ($this->appDeveloperModeFeatureFlag->isEnabled()) {
            $data = $this->getTestAppQuery->execute($id);

            if (null !== $data) {
                return App::fromTestAppValues($data);
            }
        }

        $data = $this->webMarketplaceApi->getApp($id);

        if (null === $data) {
            return null;
        }

        return App::fromWebMarketplaceValues($data);
    }
}
