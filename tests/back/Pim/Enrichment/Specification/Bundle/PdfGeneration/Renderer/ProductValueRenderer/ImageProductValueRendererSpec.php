<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class ImageProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_image_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::IMAGE)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(false);
    }

    function it_renders_null_value(
        Environment $environment,
        AttributeInterface $attribute
    ) {
        $this->render($environment, $attribute, null, 'en_US')->shouldReturn(null);
    }

    function it_renders_null_data(
        Environment $environment,
        AttributeInterface $attribute,
        MediaValueInterface $value
    ) {
        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn(null);

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn(null);
    }

    function it_renders_original_filename(
        Environment $environment,
        AttributeInterface $attribute,
        MediaValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn($fileInfo);

        $fileInfo
            ->getOriginalFilename()
            ->shouldBeCalled()
            ->willReturn('sunglasses.png');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('sunglasses.png');
    }
}
