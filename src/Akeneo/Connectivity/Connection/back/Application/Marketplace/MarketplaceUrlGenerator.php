<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\Platform\VersionProviderInterface;

final class MarketplaceUrlGenerator implements MarketplaceUrlGeneratorInterface
{
    private const START_QUERY = '/?';

    private VersionProviderInterface $versionProvider;
    private string $envUrl;
    private string $marketplaceUrl;
    private GetUserProfileQueryInterface $getUserProfileQuery;

    public function __construct(
        string $marketplaceUrl,
        VersionProviderInterface $versionProvider,
        string $envUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ) {
        if (false === filter_var($marketplaceUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('$marketplaceUrl must be a valid URL.');
        }
        $this->marketplaceUrl = $marketplaceUrl;
        $this->versionProvider = $versionProvider;
        $this->envUrl = $envUrl;
        $this->getUserProfileQuery = $getUserProfileQuery;
    }

    public function generateUrl(string $username): string
    {
        $profile = $this->getUserProfileQuery->execute($username);
        $edition = '';
        $queryToBuild = [
            'utm_medium' => 'pim',
            'utm_content' => 'marketplace_button',
            'utm_source' => $this->envUrl,
        ];
        if ($profile) {
            $queryToBuild['utm_term'] = $profile;
        }

        switch ($this->versionProvider->getEdition()) {
            case 'Serenity':
                $edition = '/discover/serenity';
                $queryToBuild['utm_campaign'] = 'connect_serenity';
                break;

            case 'GE':
                $edition = '/discover/growth-edition';
                $queryToBuild['utm_campaign'] = 'connect_ge';
                break;
        }

        $query = http_build_query($queryToBuild);

        return $this->marketplaceUrl . $edition . self::START_QUERY . $query;
    }
}
