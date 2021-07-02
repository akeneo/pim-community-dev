<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\Extension;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ExtensionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', [[
            'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
            'name' => 'Shopify connector',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
            'author' => 'Ideatarmac',
            'partner' => 'Akeneo Partner',
            'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
            'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
            'categories' => ['E-commerce'],
            'certified' => false
        ]]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Extension::class);
    }

    public function it_returns_an_identifier(): void
    {
        $this->id()->shouldBeLike(Uuid::fromString('3881aefa-16a3-4b4f-94c3-0d6e858b60b8'));
    }

    public function it_returns_a_name(): void
    {
        $this->name()->shouldBe('Shopify connector');
    }

    public function it_returns_a_logo(): void
    {
        $this->logo()->shouldBe('https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j');
    }

    public function it_returns_an_author(): void
    {
        $this->author()->shouldBe('Ideatarmac');
    }

    public function it_returns_a_partner(): void
    {
        $this->partner()->shouldBe('Akeneo Partner');
    }

    public function it_returns_a_description(): void
    {
        $this->description()->shouldBe('Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.');
    }

    public function it_returns_categories(): void
    {
        $this->categories()->shouldBe(['E-commerce']);
    }

    public function it_is_not_certified(): void
    {
        $this->certified()->shouldBe(false);
    }

    public function it_returns_a_normalized_array()
    {
        $this->normalize()->shouldBe([
            'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
            'name' => 'Shopify connector',
            'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
            'author' => 'Ideatarmac',
            'partner' => 'Akeneo Partner',
            'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
            'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
            'categories' => ['E-commerce'],
            'certified' => false
        ]);
    }
}
