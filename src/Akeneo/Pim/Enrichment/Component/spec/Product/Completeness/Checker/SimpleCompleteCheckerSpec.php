<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class SimpleCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement(ValueCompleteCheckerInterface::class);
    }

    public function it_supports_all_product_values(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_checks_empty_lists(
        ValueInterface $value,
        Collection $collection,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $value->getData()->willReturn([null, '']);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $value->getData()->willReturn($collection);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $collection->add(null);
        $collection->add('');
        $value->getData()->willReturn($collection);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_checks_complete_lists(
        ValueInterface $value,
        Collection $collection,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn([null, 'bar']);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $collection->getIterator()->willReturn(new \ArrayIterator([null, 'bar']));
        $collection->count()->willReturn(2);
        $value->getData()->willReturn($collection);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_checks_incomplete_scalars(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $value->getData()->willReturn('');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_checks_complete_scalars(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(false);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $value->getData()->willReturn(0);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $value->getData()->willReturn(0.0);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $value->getData()->willReturn('foo');
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
