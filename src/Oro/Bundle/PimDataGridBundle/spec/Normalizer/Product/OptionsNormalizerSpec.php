<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionsNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionsNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(OptionsValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_multi_select_product_value(
        OptionsValueInterface $value,
        AttributeOptionInterface $colorBlue,
        AttributeOptionInterface $colorRed,
        AttributeOptionValueInterface $optionValueBlue,
        AttributeOptionValueInterface $optionValueRed,
        $attributeOptionRepository
    ) {
        $value->getAttributeCode()->willReturn('color');
        $value->getData()->willReturn(['blue', 'red']);
        $attributeOptionRepository->findOneByIdentifier('color.blue')->willReturn($colorBlue);
        $attributeOptionRepository->findOneByIdentifier('color.red')->willReturn($colorRed);

        $colorRed->getTranslation('fr_FR')->willReturn($optionValueRed);
        $colorRed->getCode()->willReturn('red');
        $colorBlue->getTranslation('fr_FR')->willReturn($optionValueBlue);
        $optionValueBlue->getValue()->willReturn('Blue');
        $optionValueRed->getValue()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => 'Blue, [red]',
        ];

        $this->normalize($value, 'datagrid', ['data_locale' => 'fr_FR'])->shouldReturn($data);
    }
}
