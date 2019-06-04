<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductModelNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        CollectionFilterInterface $filter,
        NormalizerInterface $associationsNormalizer,
        NormalizerInterface $standardNormalizer
    ) {
        $this->beConstructedWith($filter, $associationsNormalizer, $standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_product_model_without_parent(
        $filter,
        $standardNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        WriteValueCollection $values
    ) {
        $productModel->getCode()->willReturn('code');
        $productModel->getParent()->willReturn(null);
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willReturn('family_variant');

        $productModel->getValues()->willReturn($values);

        $standardNormalizer
            ->normalize($values, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $filter->filterCollection($values, 'pim.transform.product_value.structured', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn($values);

        $productModel->getCategoryCodes()->willReturn(['tshirt']);

        $created = new \DateTime('2010-06-23');
        $productModel->getCreated()->willReturn($created);
        $standardNormalizer->normalize($created, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $productModel->getUpdated()->willReturn($updated);
        $standardNormalizer->normalize($updated, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn('2010-06-23T23:00:00+01:00');

        $this->normalize($productModel, 'standard')->shouldReturn([
            'code' => 'code',
            'family_variant' => 'family_variant',
            'parent' => null,
            'categories' => ['tshirt'],
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'value'  => 'foo',
                    ]
                ]
            ],
            'created' => '2010-06-23T00:00:00+01:00',
            'updated' => '2010-06-23T23:00:00+01:00',
            'associations' => null,
        ]);
    }

    function it_normalizes_product_model_with_parent(
        $filter,
        $standardNormalizer,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        WriteValueCollection $values,
        ProductModelInterface $parentModel
    ) {
        $productModel->getCode()->willReturn('code');
        $productModel->getParent()->willReturn($parentModel);
        $parentModel->getCode()->willReturn('parent_code');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willReturn('family_variant');

        $productModel->getValues()->willReturn($values);

        $standardNormalizer
            ->normalize($values, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $filter->filterCollection($values, 'pim.transform.product_value.structured', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn($values);

        $productModel->getCategoryCodes()->willReturn(['tshirt']);

        $created = new \DateTime('2010-06-23');
        $productModel->getCreated()->willReturn($created);
        $standardNormalizer->normalize($created, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $productModel->getUpdated()->willReturn($updated);
        $standardNormalizer->normalize($updated, 'standard', ['filter_types' => ['pim.transform.product_value.structured']])->willReturn('2010-06-23T23:00:00+01:00');

        $this->normalize($productModel, 'standard')->shouldReturn([
            'code' => 'code',
            'family_variant' => 'family_variant',
            'parent' => 'parent_code',
            'categories' => ['tshirt'],
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'value'  => 'foo',
                    ]
                ]
            ],
            'created' => '2010-06-23T00:00:00+01:00',
            'updated' => '2010-06-23T23:00:00+01:00',
            'associations' => null,
        ]);
    }

    function it_supports_standard_format(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'xml')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'csv')->shouldReturn(false);
    }
}
