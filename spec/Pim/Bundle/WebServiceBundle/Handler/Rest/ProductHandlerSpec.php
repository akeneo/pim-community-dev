<?php

namespace spec\Pim\Bundle\WebServiceBundle\Handler\Rest;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ProductHandlerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer) {
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
                'locales'  => $locales,
                'channels' => $channels,
                'resource' => $url
            ]
        )
        ->shouldBeCalled();
        $this->get($product, $channels, $locales, $url);
    }
}
