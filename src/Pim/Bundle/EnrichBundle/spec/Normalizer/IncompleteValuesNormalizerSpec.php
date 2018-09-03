<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollectionFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\ChannelTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IncompleteValuesNormalizerSpec extends ObjectBehavior
{
    function let(
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory
    ) {
        $this->beConstructedWith($requiredValueCollectionFactory, $incompleteValueCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncompleteValuesNormalizer::class);
    }

    function it_supports_entity_with_family(EntityWithFamilyInterface $entityWithFamily, CategoryInterface $category)
    {
        $this->supportsNormalization($entityWithFamily, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($entityWithFamily, 'unsupported')->shouldReturn(false);
        $this->supportsNormalization($category, 'internal_api')->shouldReturn(false);
    }

    function it_normalizes_the_incomplete_values_of_an_entity_with_family(
        $requiredValueCollectionFactory,
        $incompleteValueCollectionFactory,
        EntityWithFamilyInterface $entityWithFamily,
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

        $entityWithFamily->getFamily()->willReturn($family);
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
        $attribute->isLocalizable()->willReturn(false);

        $requiredValueCollectionFactory->forChannel($family, $channel)->willReturn($requiredValues);
        $requiredValues->filterByChannelAndLocale($channel, $locale)->willReturn($requiredValues);
        $requiredValues->count()->willReturn(1);

        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValues,
            $channel,
            $locale,
            $entityWithFamily
        )->willReturn($incompleteValues);

        $incompleteValues->attributes()->willReturn($attributes);
        $incompleteValues->count()->willReturn(1);

        $this->normalize($entityWithFamily, 'internal_api')->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'labels'  => [
                        'en_US' => 'Ecommerce',
                    ],
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => ['en_US' => 'Description'],
                                ],
                            ],
                            'label' => 'English (United States)',
                        ],
                    ],
                ]
            ]
        );
    }

    function it_returns_an_empty_array_when_the_entity_with_family_has_no_family(EntityWithFamilyInterface $entityWithFamily)
    {
        $entityWithFamily->getFamily()->willReturn(null);

        $this->normalize($entityWithFamily, 'internal_api')->shouldReturn([]);
    }
}
