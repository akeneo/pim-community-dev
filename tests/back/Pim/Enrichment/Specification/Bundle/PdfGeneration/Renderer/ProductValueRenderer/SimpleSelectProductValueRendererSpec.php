<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class SimpleSelectProductValueRendererSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_supports_simpleselect_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::OPTION_SIMPLE_SELECT)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(false);
    }

    function it_does_not_render_something_else_than_an_option(
        Environment $environment,
        AttributeInterface $attribute,
        MetricValue $value
    ) {
        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn(null);
    }

    function it_renders_option_label(
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        Environment $environment,
        AttributeInterface $attribute,
        OptionValue $value,
        AttributeOptionInterface $option,
        AttributeOptionValueInterface $translation
    ) {
        $attribute
            ->getCode()
            ->shouldBeCalled()
            ->willReturn('simpleSelectAttribute');

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn('an_option_code');

        $attributeOptionRepository
            ->findOneByIdentifier('simpleSelectAttribute.an_option_code')
            ->shouldBeCalled()
            ->willReturn($option);

        $option
            ->setLocale('en_US')
            ->shouldBeCalled();

        $option
            ->getTranslation()
            ->shouldBeCalled()
            ->willReturn($translation);

        $translation
            ->getValue()
            ->shouldBeCalled()
            ->willReturn('An option label');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('An option label');
    }
}
