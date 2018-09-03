<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\MediaExporterPathGenerator;
use PhpSpec\ObjectBehavior;

class MediaExporterPathGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaExporterPathGenerator::class);
    }

    function it_generates_the_path()
    {
        $value = [
            'locale' => null,
            'scope'  => null
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/');
    }

    function it_generates_the_path_when_the_value_is_localisable()
    {
        $value = [
            'locale' => 'fr_FR',
            'scope'  => null
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/fr_FR/');
    }

    function it_generates_the_path_when_the_value_is_scopable()
    {
        $value = [
            'locale' => null,
            'scope'  => 'ecommerce'
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/ecommerce/');
    }

    function it_generates_the_path_when_the_value_is_localisable_and_scopable()
    {
        $value = [
            'locale' => 'fr_FR',
            'scope'  => 'ecommerce'
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/fr_FR/ecommerce/');
    }

    function it_generates_the_path_when_the_sku_contains_slash()
    {
        $value = [
            'locale' => null,
            'scope'  => null
        ];

        $options = ['identifier' => 'sku/001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku_001/picture/');
    }
}
