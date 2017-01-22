<?php

namespace spec\Pim\Component\Structuring;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Template\Block;
use Pim\Component\Template\BlockInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BlockSpec extends ObjectBehavior
{
    function let(ArrayCollection $attributes)
    {
        $this->beConstructedWith($attributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Block::class);
    }

    function it_is_a_block()
    {
        $this->shouldImplement(BlockInterface::class);
    }

    function it_has_a_attributes_collection($attributes)
    {
        $this->getAttributes()->shouldReturn($attributes);
    }

    function it_add_attributes_to_the_collection(AttributeInterface $attribute, AttributeInterface $otherAttribute)
    {
        $this->addAttribute($attribute)->shouldReturn(null);
        $this->addAttribute($otherAttribute)->shouldReturn(null);

        $attributes = $this->getAttributes();
        $attributes->contains($attribute)->shouldReturn(true);
        $attributes->contains($otherAttribute)->shouldReturn(true);
        $attributes->shouldhaveCount(2);
    }
}
