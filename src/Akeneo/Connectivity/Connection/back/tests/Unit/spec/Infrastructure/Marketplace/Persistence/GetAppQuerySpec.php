<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppQuerySpec extends ObjectBehavior
{
    public function let(
        WebMarketplaceApiInterface $webMarketplaceApi,
        GetCustomAppQuery $getCustomAppQuery,
    ): void {
        $this->beConstructedWith(
            $webMarketplaceApi,
            $getCustomAppQuery,
        );
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(GetAppQuery::class);
    }

    public function it_returns_a_known_marketplace_app(
        WebMarketplaceApiInterface $webMarketplaceApi,
    ): void {
        $webMarketplaceApi->getApp('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'name' => 'Akeneo Shopware 6 App by EIKONA Media',
            'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'author' => 'EIKONA Media GmbH',
            'partner' => 'Akeneo Preferred Partner',
            'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
            'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
            'categories' => [
                'E-commerce',
            ],
            'certified' => false,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ]);

        $this->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->shouldBeLike(
            App::fromWebMarketplaceValues([
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ])
        );
    }

    public function it_returns_a_known_marketplace_app_even_when_developer_mode_is_enabled(
        WebMarketplaceApiInterface $webMarketplaceApi,
        GetCustomAppQuery $getCustomAppQuery,
    ): void {
        $getCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $webMarketplaceApi->getApp('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'name' => 'Akeneo Shopware 6 App by EIKONA Media',
            'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
            'author' => 'EIKONA Media GmbH',
            'partner' => 'Akeneo Preferred Partner',
            'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
            'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
            'categories' => [
                'E-commerce',
            ],
            'certified' => false,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ]);

        $this->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->shouldBeLike(
            App::fromWebMarketplaceValues([
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ])
        );
    }

    public function it_returns_null_if_unknown_marketplace_app(
        WebMarketplaceApiInterface $webMarketplaceApi,
    ): void {
        $webMarketplaceApi->getApp('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);

        $this->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->shouldReturn(null);
    }

    public function it_returns_a_known_custom_app_if_developer_mode_is_enabled(
        GetCustomAppQuery $getCustomAppQuery,
    ): void {
        $getCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'name' => 'My Test App',
            'author' => 'John Doe',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
        ]);

        $this->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->shouldBeLike(
            App::fromCustomAppValues([
                'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                'name' => 'My Test App',
                'author' => 'John Doe',
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ])
        );
    }

    public function it_returns_null_if_unknown_custom_app_and_marketplace_app(
        WebMarketplaceApiInterface $webMarketplaceApi,
        GetCustomAppQuery $getCustomAppQuery,
    ): void {
        $getCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $webMarketplaceApi->getApp('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);

        $this->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9')->shouldReturn(null);
    }
}
