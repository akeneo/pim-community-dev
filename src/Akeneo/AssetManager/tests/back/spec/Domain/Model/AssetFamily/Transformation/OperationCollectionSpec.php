<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use PhpSpec\ObjectBehavior;

class OperationCollectionSpec extends ObjectBehavior
{
    function it_creates_an_operation_collection(Operation $operation)
    {
        $this->beConstructedThrough('create', [[$operation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_a_collection_item_is_not_an_operation(Operation $thumbnail, Operation $resize)
    {
        $this->beConstructedThrough('create', [[$thumbnail, new \stdClass(), $resize]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_normalizes_an_operation_collection(
        Operation $operation1,
        Operation $operation2
    ) {
        $this->beConstructedThrough('create', [[$operation1, $operation2]]);
        $normalizedOperation1 = ['key' => 'value operation 1'];
        $normalizedOperation2 = ['key' => 'value operation 2'];
        $operation1->normalize()->willReturn($normalizedOperation1);
        $operation2->normalize()->willReturn($normalizedOperation2);

        $this->normalize()->shouldReturn([$normalizedOperation1, $normalizedOperation2]);
    }

    function it_can_be_instantiated_from_normalized_format()
    {
        $normalizedOperations = [
            [
                'name' => 'resize',
                'parameters' => [
                    'width' => 200,
                    'height' => 150,
                ],
            ],
        ];

        $this->beConstructedThrough('createFromNormalized', [$normalizedOperations]);
        // @TODO: obviously this test should fail (but it does not at the moment), it will force us to fix once ATR-27 is merged
        $this->getIterator()->shouldHaveCount(0);
    }
}
