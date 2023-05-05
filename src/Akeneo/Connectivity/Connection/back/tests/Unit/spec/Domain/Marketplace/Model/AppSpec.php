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
    public function let(): void
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

    public function it_is_normalizable(): void
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
            'connected' => false,
            'isPending' => false,
            'isCustomApp' => false,
        ]);
    }

    public function it_adds_analytics(): void
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
            'connected' => false,
            'isPending' => false,
            'isCustomApp' => false,
        ]);
    }

    public function it_adds_pim_url_source(): void
    {
        $this->withPimUrlSource([
            'pim_url' => 'http://my-akeneo.test',
        ])->normalize()->shouldBe([
            'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
            'name' => 'Shopify App',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'partner' => 'Akeneo',
            'description' => 'App compatible with the Akeneo Simple Activation Process that seamlessly connects Akeneo PIM to the Shopify platform.',
            'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-app',
            'categories' => ['E-commerce'],
            'certified' => false,
            'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate?pim_url=http%3A%2F%2Fmy-akeneo.test',
            'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            'connected' => false,
            'isPending' => false,
            'isCustomApp' => false,
        ]);
    }

    public function it_is_instantiable_with_custom_app_values(): void
    {
        $this->beConstructedThrough('fromCustomAppValues', [
            [
                'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
                'name' => 'Shopify App',
                'author' => 'Akeneo',
                'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
                'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            ],
        ]);

        $this->normalize()->shouldBe([
            'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
            'name' => 'Shopify App',
            'logo' => null,
            'author' => 'Akeneo',
            'partner' => null,
            'description' => null,
            'url' => null,
            'categories' => [],
            'certified' => false,
            'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
            'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            'connected' => false,
            'isPending' => false,
            'isCustomApp' => true,
        ]);
    }

    public function it_adds_pim_url_source_for_an_instance_with_custom_app_values(): void
    {
        $this->beConstructedThrough('fromCustomAppValues', [
            [
                'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
                'name' => 'Shopify App',
                'author' => 'Akeneo',
                'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate',
                'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            ],
        ]);

        $this->withPimUrlSource([
            'pim_url' => 'http://my-akeneo.test',
        ])->normalize()->shouldBe([
            'id' => 'ce8cf07f-321e-4dd2-a52f-30ac00881ba7',
            'name' => 'Shopify App',
            'logo' => null,
            'author' => 'Akeneo',
            'partner' => null,
            'description' => null,
            'url' => null,
            'categories' => [],
            'certified' => false,
            'activate_url' => 'https:\/\/fake.shopify.akeneo.com\/activate?pim_url=http%3A%2F%2Fmy-akeneo.test',
            'callback_url' => 'https:\/\/fake.shopify.akeneo.com\/oauth2\/callback',
            'connected' => false,
            'isPending' => false,
            'isCustomApp' => true,
        ]);
    }

    public function it_has_a_negative_pending_status(): void
    {
        $this->isPending()->shouldReturn(false);
    }

    public function it_has_a_positive_pending_status(): void
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
                'isPending' => true,
            ],
        ]);
        $this->isPending()->shouldReturn(true);
    }

    public function it_can_not_have_a_connected_and_a_pending_status_together(): void
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
                'isPending' => true,
                'connected' => true,
            ],
        ]);
        $this->shouldThrow(new \DomainException('An App can not be both connected and pending.'))->duringInstantiation();
    }

    public function it_provides_an_another_app_with_the_pending_status_updated(): void
    {
        $this
            ->withIsPending()
            ->normalize()
            ->shouldBe([
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
                'connected' => false,
                'isPending' => true,
                'isCustomApp' => false,
        ]);
    }

    public function it_turns_a_connected_app_into_a_pending_app(): void
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
                'connected' => true,
            ],
        ]);
        $this
            ->withIsPending()
            ->normalize()
            ->shouldBe([
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
                'connected' => false,
                'isPending' => true,
                'isCustomApp' => false,
            ]);
    }

    public function it_provides_an_another_app_with_the_connected_status_updated(): void
    {
        $this
            ->withConnectedStatus(true)
            ->normalize()
            ->shouldBe([
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
                'connected' => true,
                'isPending' => false,
                'isCustomApp' => false,
            ]);
    }
}
