<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromWebMarketplaceValues', [
            [
                'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
                'name' => 'Shopify App',
                'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                'author' => 'Akeneo',
                'partner' => 'Akeneo',
                'description' => 'App compatible with the Akeneo Simple Activation Process that seamlessly connects Akeneo PIM to the Shopify platform.',
                'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-app',
                'categories' => ['E-commerce'],
                'certified' => false,
                'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
                'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            ],
        ]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(App::class);
    }

    public function it_is_normalizable()
    {
        $this->normalize()->shouldBe([
            'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
            'name' => 'Shopify App',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'partner' => 'Akeneo',
            'description' => 'App compatible with the Akeneo Simple Activation Process that seamlessly connects Akeneo PIM to the Shopify platform.',
            'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-app',
            'categories' => ['E-commerce'],
            'certified' => false,
            'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
            'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
        ]);
    }

    public function it_adds_analytics()
    {
        $this->withAnalytics([
            'utm_campaign' => 'foobar',
        ])->normalize()->shouldBe([
            'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
            'name' => 'Shopify App',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'partner' => 'Akeneo',
            'description' => 'App compatible with the Akeneo Simple Activation Process that seamlessly connects Akeneo PIM to the Shopify platform.',
            'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-app?utm_campaign=foobar',
            'categories' => ['E-commerce'],
            'certified' => false,
            'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
            'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
        ]);
    }
}
