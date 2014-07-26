<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class MediaFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Model\ProductMedia');
    }

    function it_creates_a_media(File $file)
    {
        $this->createMedia($file)->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductMedia');
    }
}
