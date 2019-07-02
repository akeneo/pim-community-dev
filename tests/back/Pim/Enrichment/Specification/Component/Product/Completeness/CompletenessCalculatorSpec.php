<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class CompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory
    ) {
        $this->beConstructedWith(
            $requiredValueCollectionFactory,
            $incompleteValueCollectionFactory
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

    function it_calculates_completeness_for_a_product(
        $requiredValueCollectionFactory,
        $incompleteValueCollectionFactory,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeRequirementInterface $attributeRequirement,
        ChannelInterface $channel,
        LocaleInterface $locale,
        RequiredValueCollection $requiredValues,
        IncompleteValueCollection $incompleteValues,
        ValueInterface $requiredValue,
        Collection $incompleteAttributes,
        AttributeInterface $incompleteAttribute
    ) {
        $locale->getCode()->willReturn('fr_FR');
        $channel->getCode()->willReturn('ecommerce');
        $channel->getLocales()->willReturn([$locale]);
        $attributeRequirement->getChannel()->willReturn($channel);
        $family->getAttributeRequirements()->willReturn([$attributeRequirement]);

        $product->getValues()->willReturn([]);
        $product->getFamily()->willReturn($family);

        $requiredValueCollectionFactory->forChannel($family, $channel)->willReturn($requiredValues);
        $requiredValues->filterByChannelAndLocale($channel, $locale)->willReturn($requiredValues);
        $incompleteValueCollectionFactory->forChannelAndLocale(
            $requiredValues,
            $channel,
            $locale,
            $product
        )->willReturn($incompleteValues);

        $incompleteValues->getIterator()->willReturn([$requiredValue]);

        $incompleteValues->attributes()->willReturn($incompleteAttributes);
        $incompleteValues->count()->willReturn(1);
        $requiredValues->count()->willReturn(1);
        $incompleteAttributes->toArray()->willReturn([$incompleteAttribute]);
        $incompleteAttribute->getCode()->willReturn('incomplete_attribute');

        $completenesses = $this->calculate($product);
        $completenesses[0]->channelCode()->shouldEqual('ecommerce');
        $completenesses[0]->localeCode()->shouldEqual('fr_FR');
        $completenesses[0]->requiredCount()->shouldEqual(1);
        $completenesses[0]->missingAttributeCodes()->shouldEqual(['incomplete_attribute']);
        $completenesses[0]->ratio()->shouldEqual(0);
    }

    public function getMatchers(): array
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
            'containCompletenesses'     => function ($actualCompletenesses, $expectedCount) {
                return $expectedCount === count($actualCompletenesses);
            },
            'containCompleteness'     => function ($actualCompletenesses, $expectedCompleteness) {
                foreach ($actualCompletenesses as $actualCompleteness) {
                    if ($actualCompleteness->getChannel() === $expectedCompleteness->getChannel() &&
                        $actualCompleteness->getLocale() === $expectedCompleteness->getLocale() &&
                        $actualCompleteness->getMissingCount() === $expectedCompleteness->getMissingCount() &&
                        $actualCompleteness->getProduct() === $expectedCompleteness->getProduct() &&
                        $actualCompleteness->getRatio() === $expectedCompleteness->getRatio() &&
                        $actualCompleteness->getRequiredCount() === $expectedCompleteness->getRequiredCount() &&
                        $actualCompleteness->getMissingAttributes() === $expectedCompleteness->getMissingAttributes()) {
                        return true;
                    }
                }

                return false;
            },
        ];
    }
}
