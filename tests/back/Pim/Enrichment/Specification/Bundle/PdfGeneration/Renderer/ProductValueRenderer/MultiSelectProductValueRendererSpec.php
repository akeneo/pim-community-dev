<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class MultiSelectProductValueRendererSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_supports_multiselect_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::OPTION_MULTI_SELECT)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(false);
    }

    function it_does_not_render_something_else_than_an_option(
        Environment $environment,
        AttributeInterface $attribute,
        MetricValue $value
    ) {
        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn(null);
    }

    function it_renders_option_labels(
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        Environment $environment,
        AttributeInterface $attribute,
        OptionsValue $value,
        AttributeOptionInterface $option1,
        AttributeOptionValueInterface $translation1,
        AttributeOptionInterface $option2,
        AttributeOptionValueInterface $translation2
    ) {
        $attribute
            ->getCode()
            ->shouldBeCalled()
            ->willReturn('simpleSelectAttribute');

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn(['an_option_code', 'another_option_code']);

        $attributeOptionRepository
            ->findOneByIdentifier('simpleSelectAttribute.an_option_code')
            ->shouldBeCalled()
            ->willReturn($option1);

        $attributeOptionRepository
            ->findOneByIdentifier('simpleSelectAttribute.another_option_code')
            ->shouldBeCalled()
            ->willReturn($option2);

        $option1
            ->setLocale('en_US')
            ->shouldBeCalled();

        $option2
            ->setLocale('en_US')
            ->shouldBeCalled();

        $option1
            ->getTranslation()
            ->shouldBeCalled()
            ->willReturn($translation1);

        $option2
            ->getTranslation()
            ->shouldBeCalled()
            ->willReturn($translation2);

        $translation1
            ->getValue()
            ->shouldBeCalled()
            ->willReturn('An option label');

        $translation2
            ->getValue()
            ->shouldBeCalled()
            ->willReturn(null);

        $option2
            ->getCode()
            ->shouldBeCalled()
            ->willReturn('another_option_code');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('An option label, [another_option_code]');
    }
}
