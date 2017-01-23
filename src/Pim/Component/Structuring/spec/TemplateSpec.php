<?php

namespace spec\Pim\Component\Structuring;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Template\BlockInterface;
use Pim\Component\Template\Template;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TemplateSpec extends ObjectBehavior
{
    function let(ArrayCollection $blocks)
    {
        $this->beConstructedWith($blocks);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Template::class);
    }

    function it_is_a_template()
    {
        $this->shouldImplement(BlockInterface::class);
    }

    function it_has_a_blocks_collection($blocks)
    {
        $this->getBlocks()->shouldReturn($blocks);
    }

    // Do you need that?
    function it_add_attributes_to_the_collection(BlockInterface $block, BlockInterface $otherBlock)
    {
        $this->addBlock($block)->shouldReturn(null);
        $this->addBlock($otherBlock)->shouldReturn(null);

        $blocks = $this->getBlocks();
        $blocks->contains($block)->shouldReturn(true);
        $blocks->contains($otherBlock)->shouldReturn(true);
        $blocks->shouldhaveCount(2);
    }

    function it_returns_the_list_of_attributes(
        BlockInterface $block,
        BlockInterface $otherBlock,
        ArrayCollection $blockAttributes,
        ArrayCollection $otherBlockAttributes,
        \Iterator $iterator
    ) {
        $this->addBlock($block);
        $this->addBlock($otherBlock);

        $block->getAttributes($block)->shouldReturn($blockAttributes);
        $block->getAttributes($otherBlock)->shouldReturn($otherBlockAttributes);

        $otherBlockAttributes->getIterator($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willreturn($block, $otherBlock);
        $iterator->next()->shouldBeCalled();

        $blockAttributes->add($block);
        $blockAttributes->add($otherBlock);

        $this->getAttributes()->shouldReturn($blockAttributes);
    }
}
