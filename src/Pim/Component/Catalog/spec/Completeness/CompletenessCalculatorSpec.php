<?php

namespace spec\Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Completeness\CompletenessCalculator;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;

class CompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        ProductValueFactory $productValueFactory,
        CachedObjectRepositoryInterface $channelRepository,
        CachedObjectRepositoryInterface $localeRepository,
        ProductValueCompleteCheckerInterface $productValueCompleteChecker
    ) {
        $this->beConstructedWith(
            $productValueFactory,
            $channelRepository,
            $localeRepository,
            $productValueCompleteChecker,
            Completeness::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessCalculator::class);
    }

    function it_is_a_completeness_calculator()
    {
        $this->shouldImplement(CompletenessCalculatorInterface::class);
    }

    function it_does_not_calculates_completeness_for_a_product_without_family(ProductInterface $product)
    {
        $product->getFamily()->willReturn(null);

        $completenesses = $this->calculate($product);
        $completenesses->shouldBe([]);
    }

    function it_calculates_completeness_for_a_product_withouft_product_value(
        $productValueFactory,
        $channelRepository,
        $localeRepository,
        $productValueCompleteChecker,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeRequirementInterface $requirement,
        ChannelInterface $channel,
        ArrayCollection $locales,
        \ArrayIterator $localesIterator,
        LocaleInterface $locale,
        AttributeInterface $attribute,
        ProductValueCollectionInterface $requiredProductValues,
        ProductValueCollectionInterface $actualProductValues,
        ProductValueInterface $requiredProductValue
    ) {
        $attribute->isUnique()->willReturn(false);
        $channel->getCode()->willReturn('channel_code');
        $locale->getCode()->willReturn('locale_code');

        $product->getFamily()->willReturn($family);
        $family->getAttributeRequirements()->willReturn([$requirement]);
        $requirement->isRequired()->willReturn(true);
        $requirement->getChannelCode()->willReturn('channel_code');
        $requirement->getAttribute()->willReturn($attribute);

        $requirement->getChannel()->willReturn($channel);
        $channel->getLocales()->willReturn($locales);

        $locales->getIterator()->willReturn($localesIterator);
        $localesIterator->rewind()->shouldBeCalled();
        $localesIterator->valid()->willReturn(true, false);
        $localesIterator->current()->willReturn($locale);
        $localesIterator->next()->shouldBeCalled();

        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->hasLocaleSpecific($locale)->shouldNotBeCalled();
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('attribute_code');

        $productValueFactory->create($attribute, null, null, null)->willReturn($requiredProductValue);
        $channelRepository->findOneByIdentifier('channel_code')->willReturn($channel);
        $localeRepository->findOneByIdentifier('locale_code')->willReturn($locale);

        $product->getValues()->willReturn($actualProductValues);

        $requiredProductValue->getAttribute()->willReturn($attribute);
        $requiredProductValue->getScope()->willReturn('channel_code');
        $requiredProductValue->getLocale()->willReturn('locale_code');
        $requiredProductValues->getByCodes('attribute_code', 'channel_code', 'locale_code')->willReturn(null);
        $productValueCompleteChecker->isComplete()->shouldNotBeCalled();

        $completenesses = $this->calculate($product);

        $completenesses->shouldBeAnArrayOfCompletenesses();
        $completenesses->shouldContainCompletenesses(1);
    }

    function it_calculates_completeness_for_a_product_with_incomplete_product_value(
        $productValueFactory,
        $channelRepository,
        $localeRepository,
        $productValueCompleteChecker,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeRequirementInterface $requirement,
        ChannelInterface $channel,
        ArrayCollection $locales,
        \ArrayIterator $localesIterator,
        LocaleInterface $locale,
        AttributeInterface $attribute,
        ProductValueCollectionInterface $requiredProductValues,
        ProductValueCollectionInterface $actualProductValues,
        ProductValueInterface $requiredProductValue,
        ProductValueInterface $actualProductValue
    ) {
        $attribute->isUnique()->willReturn(false);
        $channel->getCode()->willReturn('channel_code');
        $locale->getCode()->willReturn('locale_code');

        $product->getFamily()->willReturn($family);
        $family->getAttributeRequirements()->willReturn([$requirement]);
        $requirement->isRequired()->willReturn(true);
        $requirement->getChannelCode()->willReturn('channel_code');
        $requirement->getAttribute()->willReturn($attribute);

        $requirement->getChannel()->willReturn($channel);
        $channel->getLocales()->willReturn($locales);

        $locales->getIterator()->willReturn($localesIterator);
        $localesIterator->rewind()->shouldBeCalled();
        $localesIterator->valid()->willReturn(true, false);
        $localesIterator->current()->willReturn($locale);
        $localesIterator->next()->shouldBeCalled();

        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->hasLocaleSpecific($locale)->shouldNotBeCalled();
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('attribute_code');

        $productValueFactory->create($attribute, null, null, null)->willReturn($requiredProductValue);
        $channelRepository->findOneByIdentifier('channel_code')->willReturn($channel);
        $localeRepository->findOneByIdentifier('locale_code')->willReturn($locale);

        $product->getValues()->willReturn($actualProductValues);

        $requiredProductValue->getAttribute()->willReturn($attribute);
        $requiredProductValue->getScope()->willReturn('channel_code');
        $requiredProductValue->getLocale()->willReturn('locale_code');
        $requiredProductValues
            ->getByCodes('attribute_code', 'channel_code', 'locale_code')
            ->willReturn($actualProductValue);
        $productValueCompleteChecker->isComplete($actualProductValue, $channel, $locale)->willReturn(false);

        $completenesses = $this->calculate($product);

        $completenesses->shouldBeAnArrayOfCompletenesses();
        $completenesses->shouldContainCompletenesses(1);
    }

    function it_calculates_completeness_for_a_product_with_complete_product_value(
        $productValueFactory,
        $channelRepository,
        $localeRepository,
        $productValueCompleteChecker,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeRequirementInterface $requirement,
        ChannelInterface $channel,
        ArrayCollection $locales,
        \ArrayIterator $localesIterator,
        LocaleInterface $locale,
        AttributeInterface $attribute,
        ProductValueCollectionInterface $requiredProductValues,
        ProductValueCollectionInterface $actualProductValues,
        ProductValueInterface $requiredProductValue,
        ProductValueInterface $actualProductValue
    ) {
        $attribute->isUnique()->willReturn(false);
        $channel->getCode()->willReturn('channel_code');
        $locale->getCode()->willReturn('locale_code');

        $product->getFamily()->willReturn($family);
        $family->getAttributeRequirements()->willReturn([$requirement]);
        $requirement->isRequired()->willReturn(true);
        $requirement->getChannelCode()->willReturn('channel_code');
        $requirement->getAttribute()->willReturn($attribute);

        $requirement->getChannel()->willReturn($channel);
        $channel->getLocales()->willReturn($locales);

        $locales->getIterator()->willReturn($localesIterator);
        $localesIterator->rewind()->shouldBeCalled();
        $localesIterator->valid()->willReturn(true, false);
        $localesIterator->current()->willReturn($locale);
        $localesIterator->next()->shouldBeCalled();

        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->hasLocaleSpecific($locale)->shouldNotBeCalled();
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('attribute_code');

        $productValueFactory->create($attribute, null, null, null)->willReturn($requiredProductValue);
        $channelRepository->findOneByIdentifier('channel_code')->willReturn($channel);
        $localeRepository->findOneByIdentifier('locale_code')->willReturn($locale);

        $product->getValues()->willReturn($actualProductValues);

        $requiredProductValue->getAttribute()->willReturn($attribute);
        $requiredProductValue->getScope()->willReturn('channel_code');
        $requiredProductValue->getLocale()->willReturn('locale_code');
        $requiredProductValues
            ->getByCodes('attribute_code', 'channel_code', 'locale_code')
            ->willReturn($actualProductValue);
        $productValueCompleteChecker->isComplete($actualProductValue, $channel, $locale)->willReturn(true);

        $completenesses = $this->calculate($product);

        $completenesses->shouldBeAnArrayOfCompletenesses();
        $completenesses->shouldContainCompletenesses(1);
    }

    function it_calculates_completeness_for_a_product_with_multiple_requirements(
        $productValueFactory,
        $channelRepository,
        $localeRepository,
        $productValueCompleteChecker,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeRequirementInterface $requirement1,
        AttributeRequirementInterface $requirement2,
        ChannelInterface $channel,
        ArrayCollection $locales,
        \ArrayIterator $localesIterator,
        LocaleInterface $locale,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        ProductValueCollectionInterface $requiredProductValues,
        ProductValueCollectionInterface $actualProductValues,
        ProductValueInterface $requiredProductValue1,
        ProductValueInterface $requiredProductValue2,
        ProductValueInterface $actualProductValue
    ) {
        $channel->getCode()->willReturn('channel_code');
        $locale->getCode()->willReturn('locale_code');

        $product->getFamily()->willReturn($family);
        $family->getAttributeRequirements()->willReturn([$requirement1, $requirement2]);
        $requirement1->isRequired()->willReturn(true);
        $requirement1->getChannelCode()->willReturn('channel_code');
        $requirement1->getAttribute()->willReturn($attribute1);
        $requirement1->getChannel()->willReturn($channel);
        $requirement2->isRequired()->willReturn(true);
        $requirement2->getChannelCode()->willReturn('channel_code');
        $requirement2->getAttribute()->willReturn($attribute2);
        $requirement2->getChannel()->willReturn($channel);
        $channel->getLocales()->willReturn($locales);

        $locales->getIterator()->willReturn($localesIterator);
        $localesIterator->rewind()->shouldBeCalled();
        $localesIterator->valid()->willReturn(true, false);
        $localesIterator->current()->willReturn($locale);
        $localesIterator->next()->shouldBeCalled();

        $attribute1->isUnique()->willReturn(false);
        $attribute1->isLocaleSpecific()->willReturn(false);
        $attribute1->hasLocaleSpecific($locale)->shouldNotBeCalled();
        $attribute1->isScopable()->willReturn(false);
        $attribute1->isLocalizable()->willReturn(false);
        $attribute1->getCode()->willReturn('attribute_code_1');

        $attribute2->isUnique()->willReturn(false);
        $attribute2->isLocaleSpecific()->willReturn(false);
        $attribute2->hasLocaleSpecific($locale)->shouldNotBeCalled();
        $attribute2->isScopable()->willReturn(false);
        $attribute2->isLocalizable()->willReturn(false);
        $attribute2->getCode()->willReturn('attribute_code_2');

        $productValueFactory->create($attribute1, null, null, null)->willReturn($requiredProductValue1);
        $productValueFactory->create($attribute2, null, null, null)->willReturn($requiredProductValue2);
        $channelRepository->findOneByIdentifier('channel_code')->willReturn($channel);
        $localeRepository->findOneByIdentifier('locale_code')->willReturn($locale);

        $product->getValues()->willReturn($actualProductValues);

        $requiredProductValue1->getAttribute()->willReturn($attribute1);
        $requiredProductValue1->getScope()->willReturn('channel_code');
        $requiredProductValue1->getLocale()->willReturn('locale_code');
        $requiredProductValues
            ->getByCodes('attribute_code_1', 'channel_code', 'locale_code')
            ->willReturn($actualProductValue);
        $productValueCompleteChecker->isComplete($actualProductValue, $channel, $locale)->willReturn(false);

        $requiredProductValue2->getAttribute()->willReturn($attribute1);
        $requiredProductValue2->getScope()->willReturn('channel_code');
        $requiredProductValue2->getLocale()->willReturn('locale_code');
        $requiredProductValues->getByCodes('attribute_code_2', 'channel_code', 'locale_code')->willReturn(null);

        $completenesses = $this->calculate($product);

        $completenesses->shouldBeAnArrayOfCompletenesses();
        $completenesses->shouldContainCompletenesses(1);
    }

    public function getMatchers()
    {
        return [
            'beAnArrayOfCompletenesses' => function ($completenesses) {
                $containsCompletenesses = true;
                foreach ($completenesses as $completeness) {
                    if (!$completeness instanceof CompletenessInterface) {
                        $containsCompletenesses = false;
                    }
                }

                return !empty($completenesses) && true === $containsCompletenesses;
            },
            'containCompletenesses'     => function ($completenesses, $number) {
                return $number === count($completenesses);
            },
        ];
    }
}
