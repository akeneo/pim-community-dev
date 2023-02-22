<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\Model;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectedAppSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
            true,
            false,
            true,
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConnectedApp::class);
    }

    public function it_returns_the_id(): void
    {
        $this->getId()->shouldBe('4028c158-d620-4903-9859-958b66a059e2');
    }

    public function it_returns_the_name(): void
    {
        $this->getName()->shouldBe('Example App');
    }

    public function it_returns_the_scopes(): void
    {
        $this->getScopes()->shouldBe(['Scope1', 'Scope2']);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->getConnectionCode()->shouldBe('someConnectionCode');
    }

    public function it_returns_the_logo(): void
    {
        $this->getLogo()->shouldBe('https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC');
    }

    public function it_returns_the_author(): void
    {
        $this->getAuthor()->shouldBe('Akeneo');
    }

    public function it_returns_the_user_group_name(): void
    {
        $this->getUserGroupName()->shouldBe('app_123456abcdef');
    }

    public function it_returns_the_connection_username(): void
    {
        $this->getConnectionUsername()->shouldBe('an_username');
    }

    public function it_returns_the_categories(): void
    {
        $this->getCategories()->shouldBe(['E-commerce', 'print']);
    }

    public function it_returns_the_certified_status(): void
    {
        $this->isCertified()->shouldBe(true);
    }

    public function it_returns_the_partner(): void
    {
        $this->getPartner()->shouldBe('Akeneo partner');
    }

    public function it_returns_the_outdated_scopes_status(): void
    {
        $this->hasOutdatedScopes()->shouldBe(true);
    }

    public function it_could_be_pending(): void
    {
        $this->beConstructedWith(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
            false,
            true,
        );
        $this->isPending()->shouldReturn(true);
    }

    public function it_is_normalizable(): void
    {
        $this->normalize()->shouldBe([
            'id' => '4028c158-d620-4903-9859-958b66a059e2',
            'name' => 'Example App',
            'scopes' => ['Scope1', 'Scope2'],
            'connection_code' => 'someConnectionCode',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'user_group_name' => 'app_123456abcdef',
            'connection_username' => 'an_username',
            'categories' => ['E-commerce', 'print'],
            'certified' => true,
            'partner' => 'Akeneo partner',
            'is_custom_app' => true,
            'is_pending' => false,
            'has_outdated_scopes' => true,
        ]);
    }

    public function it_is_neither_a_custom_app_nor_pending_by_default(): void
    {
        $this->beConstructedWith(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
        );

        $this->normalize()->shouldBe([
            'id' => '4028c158-d620-4903-9859-958b66a059e2',
            'name' => 'Example App',
            'scopes' => ['Scope1', 'Scope2'],
            'connection_code' => 'someConnectionCode',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'user_group_name' => 'app_123456abcdef',
            'connection_username' => 'an_username',
            'categories' => ['E-commerce', 'print'],
            'certified' => true,
            'partner' => 'Akeneo partner',
            'is_custom_app' => false,
            'is_pending' => false,
            'has_outdated_scopes' => false,
        ]);
    }

    public function it_updates_description_properties(): void
    {
        $updated = $this->withUpdatedDescription(
            'New Name',
            'http://example.com/new-logo.png',
            'New Author',
            ['new category'],
            true,
            'Akeneo Premium Partner',
        );

        $updated->normalize()->shouldBe([
            'id' => '4028c158-d620-4903-9859-958b66a059e2',
            'name' => 'New Name',
            'scopes' => ['Scope1', 'Scope2'],
            'connection_code' => 'someConnectionCode',
            'logo' => 'http://example.com/new-logo.png',
            'author' => 'New Author',
            'user_group_name' => 'app_123456abcdef',
            'connection_username' => 'an_username',
            'categories' => ['new category'],
            'certified' => true,
            'partner' => 'Akeneo Premium Partner',
            'is_custom_app' => true,
            'is_pending' => false,
            'has_outdated_scopes' => true,
        ]);
    }

    public function it_has_not_outdated_scopes_by_default(): void
    {
        $this->beConstructedWith(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
        );

        $this->normalize()->shouldBe([
            'id' => '4028c158-d620-4903-9859-958b66a059e2',
            'name' => 'Example App',
            'scopes' => ['Scope1', 'Scope2'],
            'connection_code' => 'someConnectionCode',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'user_group_name' => 'app_123456abcdef',
            'connection_username' => 'an_username',
            'categories' => ['E-commerce', 'print'],
            'certified' => true,
            'partner' => 'Akeneo partner',
            'is_custom_app' => false,
            'is_pending' => false,
            'has_outdated_scopes' => false,
        ]);
    }
}
