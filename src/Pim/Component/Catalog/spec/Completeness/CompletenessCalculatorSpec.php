<?php

namespace spec\Pim\Component\Catalog\Completeness;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessCalculator;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollection;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollection;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class CompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        RequiredValueCollectionFactory $requiredValueCollectionFactory,
        IncompleteValueCollectionFactory $incompleteValueCollectionFactory
    ) {
        $this->beConstructedWith(
            $requiredValueCollectionFactory,
            $incompleteValueCollectionFactory,
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
        CompletenessInterface $expectedCompleteness
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

        $expectedCompleteness->getChannel()->willReturn($channel);
        $expectedCompleteness->getLocale()->willReturn($locale);
        $expectedCompleteness->getMissingCount()->willReturn(1);
        $expectedCompleteness->getProduct()->willReturn($product);
        $expectedCompleteness->getRatio()->willReturn(0);
        $expectedCompleteness->getRequiredCount()->willReturn(1);
        $expectedCompleteness->getMissingAttributes()->willReturn($incompleteAttributes);

        $completenesses = $this->calculate($product);
        $completenesses->shouldBeAnArrayOfCompletenesses();
        $completenesses->shouldContainCompletenesses(1);
        $completenesses->shouldContainCompleteness($expectedCompleteness);
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
