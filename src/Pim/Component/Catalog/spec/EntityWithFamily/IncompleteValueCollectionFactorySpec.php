<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollectionFactory;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;

class IncompleteValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        ValueCompleteCheckerInterface $completeValueChecker,
        AttributeInterface $price,
        AttributeInterface $description,
        AttributeInterface $name,
        ChannelInterface $ecommerce,
        LocaleInterface $en_US
    ) {
        $price->isUnique()->willReturn(false);
        $description->isUnique()->willReturn(false);
        $name->isUnique()->willReturn(false);

        $price->getCode()->willReturn('price');
        $price->isScopable()->willReturn(true);
        $price->isLocalizable()->willReturn(false);
        $description->getCode()->willReturn('description');
        $description->isScopable()->willReturn(true);
        $description->isLocalizable()->willReturn(true);
        $name->getCode()->willReturn('name');
        $name->isScopable()->willReturn(false);
        $name->isLocalizable()->willReturn(true);

        $ecommerce->getCode()->willReturn('ecommerce');
        $en_US->getCode()->willReturn('en_US');

        $this->beConstructedWith($completeValueChecker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncompleteValueCollectionFactory::class);
    }

    function it_creates_incomplete_values(
        $completeValueChecker,
        $en_US,
        $description,
        $price,
        $name,
        $ecommerce,
        EntityWithFamilyInterface $product,
        ValueCollectionInterface $productValues,
        \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface $requiredValues,
        RequiredValueCollectionInterface $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        ValueInterface $requiredValue1,
        ValueInterface $requiredValue2,
        ValueInterface $requiredValue3,
        ValueInterface $productValue1,
        ValueInterface $productValue3
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, true, true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn(
            $requiredValue1,
            $requiredValue2,
            $requiredValue3
        );
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue1->getAttribute()->willReturn($price);
        $requiredValue1->getScope()->willReturn('ecommerce');
        $requiredValue1->getLocale()->willReturn(null);

        $requiredValue2->getAttribute()->willReturn($description);
        $requiredValue2->getScope()->willReturn('ecommerce');
        $requiredValue2->getLocale()->willReturn('en_US');

        $requiredValue3->getAttribute()->willReturn($name);
        $requiredValue3->getScope()->willReturn(null);
        $requiredValue3->getLocale()->willReturn('en_US');

        $product->getValues()->willReturn($productValues);
        $productValues->getSame($requiredValue1)->willReturn($productValue1);
        $productValues->getSame($requiredValue2)->willReturn(null);
        $productValues->getSame($requiredValue3)->willReturn($productValue3);

        $completeValueChecker->isComplete($productValue1, $ecommerce, $en_US)->willReturn(true);
        $completeValueChecker->isComplete($productValue3, $ecommerce, $en_US)->willReturn(false);

        $incompleteValues = $this->forChannelAndLocale($requiredValues, $ecommerce, $en_US, $product);
        $incompleteValues->count()->shouldReturn(2);
        $incompleteValues->hasSame($requiredValue2)->shouldReturn(true);
        $incompleteValues->hasSame($requiredValue3)->shouldReturn(true);
    }

    function it_creates_incomplete_values_when_the_entity_has_empty_values(
        $completeValueChecker,
        $en_US,
        $price,
        $ecommerce,
        EntityWithFamilyInterface $product,
        ValueCollectionInterface $productValues,
        RequiredValueCollectionInterface $requiredValues,
        \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        ValueInterface $requiredValue,
        ValueInterface $productValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->getAttribute()->willReturn($price);
        $requiredValue->getScope()->willReturn('ecommerce');
        $requiredValue->getLocale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getSame($requiredValue)->willReturn($productValue);

        $completeValueChecker->isComplete($productValue, $ecommerce, $en_US)->willReturn(false);

        $incompleteValues = $this->forChannelAndLocale($requiredValues, $ecommerce, $en_US, $product);
        $incompleteValues->count()->shouldReturn(1);
        $incompleteValues->hasSame($requiredValue)->shouldReturn(true);
    }

    function it_creates_incomplete_values_when_the_entity_has_missing_values(
        $completeValueChecker,
        $en_US,
        $price,
        $ecommerce,
        EntityWithFamilyInterface $product,
        ValueCollectionInterface $productValues,
        \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface $requiredValues,
        RequiredValueCollectionInterface $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        ValueInterface $requiredValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->getAttribute()->willReturn($price);
        $requiredValue->getScope()->willReturn('ecommerce');
        $requiredValue->getLocale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getSame($requiredValue)->willReturn(null);

        $completeValueChecker->isComplete(Argument::cetera())->shouldNotBeCalled();

        $incompleteValues = $this->forChannelAndLocale($requiredValues, $ecommerce, $en_US, $product);
        $incompleteValues->count()->shouldReturn(1);
        $incompleteValues->hasSame($requiredValue)->shouldReturn(true);
    }

    function it_creates_empty_collection_when_the_entity_has_all_required_values(
        $completeValueChecker,
        $en_US,
        $price,
        $ecommerce,
        EntityWithFamilyInterface $product,
        ValueCollectionInterface $productValues,
        \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface $requiredValues,
        \Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionInterface $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        ValueInterface $requiredValue,
        ValueInterface $productValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->getAttribute()->willReturn($price);
        $requiredValue->getScope()->willReturn('ecommerce');
        $requiredValue->getLocale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getSame($requiredValue)->willReturn($productValue);

        $completeValueChecker->isComplete($productValue, $ecommerce, $en_US)->willReturn(true);

        $incompleteValues = $this->forChannelAndLocale($requiredValues, $ecommerce, $en_US, $product);
        $incompleteValues->count()->shouldReturn(0);
    }
}
