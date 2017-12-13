<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use PimEnterprise\Component\ProductAsset\Updater\TagUpdater;

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
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Component\ProductAsset\Model\TagInterface'
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
