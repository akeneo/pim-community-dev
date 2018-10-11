<?php

namespace Specification\Akeneo\Asset\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\TagInterface;
use Akeneo\Asset\Component\Updater\TagUpdater;

class TagUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TagUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_tag()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                TagInterface::class
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

    function it_throws_exception_when_code_is_not_scalar(TagInterface $tag)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::scalarExpected('code', TagUpdater::class, [])
        )->during(
            'update',
            [$tag, ['code' => []]]
        );
    }

    function it_throws_exception_when_a_property_is_unknown(TagInterface $tag)
    {
        $this->shouldThrow(
            UnknownPropertyException::unknownProperty('michel')
        )->during(
            'update',
            [$tag, ['michel' => 'michel']]
        );
    }
}
