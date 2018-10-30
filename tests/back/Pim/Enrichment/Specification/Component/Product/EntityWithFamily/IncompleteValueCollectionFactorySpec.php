<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValue;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
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
        RequiredValueCollection $requiredValues,
        RequiredValueCollection $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        RequiredValue $requiredValue1,
        RequiredValue $requiredValue2,
        RequiredValue $requiredValue3,
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

        $requiredValue1->forAttribute()->willReturn($price);
        $requiredValue1->forChannel()->willReturn($ecommerce);
        $requiredValue1->forLocale()->willReturn($en_US);
        $requiredValue1->attribute()->willReturn('price');
        $requiredValue1->channel()->willReturn('ecommerce');
        $requiredValue1->locale()->willReturn(null);

        $requiredValue2->forAttribute()->willReturn($description);
        $requiredValue2->forChannel()->willReturn('ecommerce');
        $requiredValue2->forLocale()->willReturn('en_US');
        $requiredValue2->attribute()->willReturn('description');
        $requiredValue2->channel()->willReturn('ecommerce');
        $requiredValue2->locale()->willReturn('en_US');

        $requiredValue3->forAttribute()->willReturn($name);
        $requiredValue3->forChannel()->willReturn($en_US);
        $requiredValue3->forLocale()->willReturn('en_US');
        $requiredValue3->attribute()->willReturn('name');
        $requiredValue3->channel()->willReturn(null);
        $requiredValue3->locale()->willReturn('en_US');

        $product->getValues()->willReturn($productValues);
        $productValues->getByCodes('price', 'ecommerce', null)->willReturn($productValue1);
        $productValues->getByCodes('description', 'ecommerce', 'en_US')->willReturn(null);
        $productValues->getByCodes('name', null, 'en_US')->willReturn($productValue3);

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
        RequiredValueCollection $requiredValues,
        RequiredValueCollection $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        RequiredValue $requiredValue,
        ValueInterface $productValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->forAttribute()->willReturn($price);
        $requiredValue->forChannel()->willReturn('ecommerce');
        $requiredValue->forLocale()->willReturn($en_US);
        $requiredValue->attribute()->willReturn('price');
        $requiredValue->channel()->willReturn('ecommerce');
        $requiredValue->locale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getByCodes('price', 'ecommerce', null)->willReturn($productValue);

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
        RequiredValueCollection $requiredValues,
        RequiredValueCollection $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        RequiredValue $requiredValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->forAttribute()->willReturn($price);
        $requiredValue->forChannel()->willReturn('ecommerce');
        $requiredValue->forLocale()->willReturn($en_US);
        $requiredValue->attribute()->willReturn('price');
        $requiredValue->channel()->willReturn('ecommerce');
        $requiredValue->locale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getByCodes('price', 'ecommerce', null)->willReturn(null);

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
        RequiredValueCollection $requiredValues,
        RequiredValueCollection $requiredValuesForChannelAndLocale,
        \Iterator $requiredValuesForChannelAndLocaleIterator,
        RequiredValue $requiredValue,
        ValueInterface $productValue
    ) {
        $requiredValues->filterByChannelAndLocale($ecommerce, $en_US)->willReturn($requiredValuesForChannelAndLocale);
        $requiredValuesForChannelAndLocale->getIterator()->willReturn($requiredValuesForChannelAndLocaleIterator);
        $requiredValuesForChannelAndLocaleIterator->rewind()->shouldBeCalled();
        $requiredValuesForChannelAndLocaleIterator->valid()->willReturn(true, false);
        $requiredValuesForChannelAndLocaleIterator->current()->willReturn($requiredValue);
        $requiredValuesForChannelAndLocaleIterator->next()->shouldBeCalled();

        $requiredValue->forAttribute()->willReturn($price);
        $requiredValue->forChannel()->willReturn('ecommerce');
        $requiredValue->forLocale()->willReturn($en_US);
        $requiredValue->attribute()->willReturn('price');
        $requiredValue->channel()->willReturn('ecommerce');
        $requiredValue->locale()->willReturn(null);

        $product->getValues()->willReturn($productValues);
        $productValues->getByCodes('price', 'ecommerce', null)->willReturn($productValue);

        $completeValueChecker->isComplete($productValue, $ecommerce, $en_US)->willReturn(true);

        $incompleteValues = $this->forChannelAndLocale($requiredValues, $ecommerce, $en_US, $product);
        $incompleteValues->count()->shouldReturn(0);
    }
}
