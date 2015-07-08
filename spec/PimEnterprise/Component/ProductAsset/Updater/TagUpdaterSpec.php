<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Prophecy\Argument;

class TagUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Updater\TagUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_tag()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "PimEnterprise\Component\ProductAsset\Model\TagInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_tag(TagInterface $tag)
    {
        $tag->setCode('mycode')->shouldBeCalled();
        $tag->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
        ];

        $this->update($tag, $values, []);
    }
}
