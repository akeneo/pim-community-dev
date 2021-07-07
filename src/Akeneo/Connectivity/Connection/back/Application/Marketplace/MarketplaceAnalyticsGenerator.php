<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\VersionProviderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGenerator
{
    private GetUserProfileQueryInterface $getUserProfileQuery;
    private VersionProviderInterface $versionProvider;
    private PimUrl $pimUrl;

    public function __construct(
        GetUserProfileQueryInterface $getUserProfileQuery,
        VersionProviderInterface $versionProvider,
        PimUrl $pimUrl
    ) {
        $this->getUserProfileQuery = $getUserProfileQuery;
        $this->versionProvider = $versionProvider;
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

        switch ($this->versionProvider->getEdition()) {
            case 'Serenity':
                $queryParameters['utm_campaign'] = 'connect_serenity';
                break;

            case 'GE':
                $queryParameters['utm_campaign'] = 'connect_ge';
                break;
        }

        return $queryParameters;
    }
}
