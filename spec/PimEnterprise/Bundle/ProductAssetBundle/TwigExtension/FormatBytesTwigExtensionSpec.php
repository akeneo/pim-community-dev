<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\TwigExtension;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;

class FormatBytesTwigExtensionSpec extends ObjectBehavior
{

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('twig_extension');
    }

    function it_converts()
    {
        $this->formatBytes(753)->shouldReturn('753 B');
        $this->formatBytes(7532)->shouldReturn('7.36 KB');
        $this->formatBytes(7532456)->shouldReturn('7.18 MB');
        $this->formatBytes(7532456345)->shouldReturn('7.02 GB');
        $this->formatBytes(75324563432535)->shouldReturn('68.51 TB');

        $this->formatBytes(753, 3)->shouldReturn('753 B');
        $this->formatBytes(7532, 3)->shouldReturn('7.355 KB');
        $this->formatBytes(7532456, 3)->shouldReturn('7.184 MB');
        $this->formatBytes(7532456345, 3)->shouldReturn('7.015 GB');
        $this->formatBytes(75324563432535, 3)->shouldReturn('68.507 TB');

        $this->formatBytes(753, 2, true)->shouldReturn('753 B');
        $this->formatBytes(7532, 2, true)->shouldReturn('7.53 K');
        $this->formatBytes(7532456, 2, true)->shouldReturn('7.53 M');
        $this->formatBytes(7532456345, 2, true)->shouldReturn('7.53 G');
        $this->formatBytes(75324563432535, 2, true)->shouldReturn('75.32 T');
    }
}
