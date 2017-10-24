<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\EnrichBundle\Normalizer\ProductModelIncompleteValuesNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollection;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollection;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\AttributeTranslationInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ChannelTranslationInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelIncompleteValuesNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory
    ) {
        $this->beConstructedWith($normalizer, $requiredValueCollectionFactory, $incompleteValueCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelIncompleteValuesNormalizer::class);
    }

    function it_supports_product_model(ProductModelInterface $productModel, ProductInterface $product)
    {
        $this->supportsNormalization($productModel, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($productModel, 'unsupported')->shouldReturn(false);
        $this->supportsNormalization($product, 'internal_api')->shouldReturn(false);
    }

    function it_normalizes_the_incomplete_values_of_a_product_model(
        $requiredValueCollectionFactory,
        $incompleteValueCollectionFactory,
        ProductModelInterface $productModel,
        AttributeRequirementInterface $attributeRequirement,
        FamilyInterface $family,
        ChannelInterface $channel,
        ChannelTranslationInterface $channelTranslation,
        Collection $locales,
        LocaleInterface $locale,
        \Iterator $localesIterator,
        RequiredValueCollection $requiredValues,
        IncompleteValueCollection $incompleteValues,
        Collection $attributes,
        AttributeInterface $attribute,
        \Iterator $attributesIterator,
        AttributeTranslationInterface $attributeTranslation
    ) {

        $productModel->getFamily()->willReturn($family);
        $family->getAttributeRequirements()->willReturn([$attributeRequirement]);
        $attributeRequirement->getChannel()->willReturn($channel);
        $attributeRequirement->isRequired()->willReturn(true);

        $channelTranslation->getLabel()->willReturn('Ecommerce');
        $channel->getCode()->willReturn('ecommerce');
        $channel->getLocales()->willReturn($locales);
        $channel->getTranslation('en_US')->willReturn($channelTranslation);

        $localesIterator->rewind()->shouldBeCalled();
        $localesIterator->next()->shouldBeCalled();
        $localesIterator->current()->willReturn($locale);
        $localesIterator->valid()->willReturn(true, false);
        $locales->getIterator()->willReturn($localesIterator);
        $locales->toArray()->willReturn([$locale]);
        $locale->getCode()->willReturn('en_US');
        $locale->getName()->willReturn('English (United States)');

        $attributeTranslation->getLabel()->willReturn('Description');
        $attributesIterator->rewind()->shouldBeCalled();
        $attributesIterator->next()->shouldBeCalled();
        $attributesIterator->current()->willReturn($attribute);
        $attributesIterator->valid()->willReturn(true, false);
        $attributes->getIterator()->willReturn($attributesIterator);
        $attribute->getCode()->willReturn('description');
        $attribute->getTranslation('en_US')->willReturn($attributeTranslation);

        $requiredValueCollectionFactory->forChannel($family, $channel)->willReturn($requiredValues);
        $requiredValues->filterByChannelAndLocale($channel, $locale)->willReturn($requiredValues);
        $requiredValues->count()->willReturn(1);

        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValues,
            $channel,
            $locale,
            $productModel
        )->willReturn($incompleteValues);

        $incompleteValues->attributes()->willReturn($attributes);
        $incompleteValues->count()->willReturn(1);

        $this->normalize($productModel, 'internal_api')->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'labels'  => [
                        'en_US' => 'Ecommerce',
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => [
                                'required' => 1,
                                'missing'  => 1,
                                'ratio'    => null,
                                'locale'   => 'en_US',
                                'channel'  => 'ecommerce',
                            ],
                            'missing'      => [
                                [
                                    'code'   => 'description',
                                    'labels' => ['en_US' => 'Description'],
                                ],
                            ],
                            'label'        => 'English (United States)',
                        ],
                    ],
                ]
            ]
        );
    }

    function it_returns_an_empty_array_when_the_product_model_has_no_family(ProductModelInterface $productModel)
    {
        $productModel->getFamily()->willReturn(null);

        $this->normalize($productModel, 'internal_api')->shouldReturn([]);
    }
}
