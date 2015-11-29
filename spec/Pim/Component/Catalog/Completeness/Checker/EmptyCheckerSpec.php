<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductMediaInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class EmptyCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_supports_empty_value(
        ProductValueInterface $productValue
    ) {
        $productValue->getData()->willReturn(null);
        $this->supportsValue($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn('');
        $this->supportsValue($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn([]);
        $this->supportsValue($productValue)->shouldReturn(true);

        $productValue->getData()->willReturn(new \ArrayObject());
        $this->supportsValue($productValue)->shouldReturn(true);
    }

    public function it_rejects_empty_value(
        ProductValueInterface $productValue
    ) {
        $productValue->getData()->willReturn(null);
        $this->supportsValue($productValue)->shouldReturn(true);
        $this->isComplete($productValue)->shouldReturn(false);
    }
}
