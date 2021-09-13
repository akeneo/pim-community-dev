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
    public function let()
    {
        $this->beConstructedWith(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
            'https:\/\/www.example.com\/some\/app\/url'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConnectedApp::class);
    }

    public function it_is_normalizable()
    {
        $this->normalize()->shouldBe([
            'id' => '4028c158-d620-4903-9859-958b66a059e2',
            'name' => 'Example App',
            'scopes' => ['Scope1', 'Scope2'],
            'connection_code' => 'someConnectionCode',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'author' => 'Akeneo',
            'categories' => ['E-commerce', 'print'],
            'certified' => true,
            'partner' => 'Akeneo partner',
        ]);
    }
}
