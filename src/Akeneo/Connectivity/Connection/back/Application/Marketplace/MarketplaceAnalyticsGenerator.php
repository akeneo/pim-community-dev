<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGenerator
{
    public function __construct(private GetUserProfileQueryInterface $getUserProfileQuery, private WebMarketplaceAliasesInterface $webMarketplaceAliases, private PimUrl $pimUrl)
    {
    }

    /**
     * @return array{utm_medium: string, utm_content: string, utm_source: string, utm_term?: string, utm_campaign?: string}
     */
    public function getExtensionQueryParameters(string $username): array
    {
        $profile = $this->getUserProfileQuery->execute($username);

        $queryParameters = [
            'utm_medium' => 'pim',
            'utm_content' => 'extension_link',
            'utm_source' => $this->pimUrl->getPimUrl(),
        ];
        if ($profile) {
            $queryParameters['utm_term'] = $profile;
        }

        $campaign = $this->webMarketplaceAliases->getUtmCampaign();
        if (null !== $campaign) {
            $queryParameters['utm_campaign'] = $campaign;
        }

        return $queryParameters;
    }
}
