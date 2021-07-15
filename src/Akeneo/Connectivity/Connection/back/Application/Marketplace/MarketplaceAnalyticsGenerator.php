<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGenerator
{
    private GetUserProfileQueryInterface $getUserProfileQuery;
    private WebMarketplaceAliasesInterface $webMarketplaceAliases;
    private PimUrl $pimUrl;

    public function __construct(
        GetUserProfileQueryInterface $getUserProfileQuery,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        PimUrl $pimUrl
    ) {
        $this->getUserProfileQuery = $getUserProfileQuery;
        $this->webMarketplaceAliases = $webMarketplaceAliases;
        $this->pimUrl = $pimUrl;
    }

    /**
     * @return array<string, string>
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
