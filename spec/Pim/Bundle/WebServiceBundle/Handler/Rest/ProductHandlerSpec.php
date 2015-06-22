<?php

namespace spec\Pim\Bundle\WebServiceBundle\Handler\Rest;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductHandlerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\WebServiceBundle\Handler\Rest\ProductHandler');
    }

    function it_gets_a_product($serializer, ProductInterface $product)
    {
        $channels = ['ecommerce', 'print'];
        $locales = ['en_US', 'fr_FR'];
        $url = 'resource/url';
        $serializer->serialize(
            $product,
            'json',
            [
                'locales'     => $locales,
                'channels'    => $channels,
                'resource'    => $url,
                'filter_type' => 'pim.external_api.product.view'
            ]
        )
        ->shouldBeCalled();
        $this->get($product, $channels, $locales, $url);
    }
}
