<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;

final class MarketplaceUrlGenerator implements MarketplaceUrlGeneratorInterface
{
    private const START_QUERY = '/?';

    private string $envUrl;
    private string $marketplaceUrl;
    private GetUserProfileQueryInterface $getUserProfileQuery;

    public function __construct(
        string $marketplaceUrl,
        string $envUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ) {
        if (false === \filter_var($marketplaceUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('$marketplaceUrl must be a valid URL.');
        }
        $this->marketplaceUrl = $marketplaceUrl;
        $this->envUrl = $envUrl;
        $this->getUserProfileQuery = $getUserProfileQuery;
    }

    public function generateUrl(string $username): string
    {
        $profile = $this->getUserProfileQuery->execute($username);
        $queryToBuild = [
            'utm_medium' => 'pim',
            'utm_content' => 'marketplace_button',
            'utm_source' => $this->envUrl,
        ];
        if ($profile) {
            $queryToBuild['utm_term'] = $profile;
        }

        $query = \http_build_query($queryToBuild);

        return $this->marketplaceUrl . self::START_QUERY . $query;
    }
}
