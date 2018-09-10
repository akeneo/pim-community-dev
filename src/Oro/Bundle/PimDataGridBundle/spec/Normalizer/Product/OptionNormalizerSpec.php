<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Prophecy\Argument;

class OptionNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }


    function it_supports_datagrid_format_and_product_value(OptionValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_simple_select_product_value_with_label(
        OptionValueInterface $value,
        AttributeOptionInterface $purpleOption,
        AttributeOptionValueInterface $purpleOptionValue,
        $attributeOptionRepository
    ) {
        $value->getAttributeCode()->willReturn('color');
        $value->getData()->willReturn('purple');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);

        $attributeOptionRepository->findOneByIdentifier('color.purple')->willReturn($purpleOption);

        $purpleOption->setLocale('fr_FR')->shouldBeCalled();
        $purpleOption->getTranslation()->willReturn($purpleOptionValue);
        $purpleOptionValue->getValue()->willReturn('Violet');


        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Violet',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_an_simple_select_product_value_without_label(
        OptionValueInterface $value,
        AttributeOptionInterface $purpleOption,
        AttributeOptionValueInterface $purpleOptionValue,
        $attributeOptionRepository
    ) {
        $value->getAttributeCode()->willReturn('color');
        $value->getData()->willReturn('purple');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);

        $attributeOptionRepository->findOneByIdentifier('color.purple')->willReturn($purpleOption);

        $purpleOption->setLocale('fr_FR')->shouldBeCalled();
        $purpleOption->getTranslation()->willReturn($purpleOptionValue);
        $purpleOption->getCode()->willReturn('purple');
        $purpleOptionValue->getValue()->willReturn(null);
        $purpleOptionValue->getValue()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '[purple]',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }

    function it_normalizes_a_simple_select_product_value_without_data(
        OptionValueInterface $value,
        AttributeOptionInterface $purpleOption
    ) {
        $value->getAttributeCode()->willReturn('color');
        $value->getData()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);

        $purpleOption->setLocale(Argument::any())->shouldNotBeCalled();
        $purpleOption->getTranslation()->shouldNotBeCalled();

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => '',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
