<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplierRegistry;
use PhpSpec\ObjectBehavior;

class OperationApplierRegistrySpec extends ObjectBehavior
{
    function it_is_a_registry_of_operation_appliers()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(OperationApplierRegistry::class);
    }

    function it_can_only_register_operation_appliers()
    {
        $this->beConstructedWith([new \stdClass()]);
        $this->shouldThrow(\TypeError::class)->duringInstantiation();
    }

    function it_gets_an_applier_for_an_operation(
        OperationApplier $colorspaceApplier,
        OperationApplier $thumbnailApplier,
        Operation $operation
    ) {
        $this->beConstructedWith([$colorspaceApplier, $thumbnailApplier]);
        $colorspaceApplier->supports($operation)->willReturn(false);
        $thumbnailApplier->supports($operation)->willReturn(true);

        $this->getApplier($operation)->shouldReturn($thumbnailApplier);
    }

    function it_throws_an_exception_when_operation_applier_cannot_be_found(
        OperationApplier $colorspaceApplier,
        OperationApplier $thumbnailApplier
    ) {
        $operation = ResizeOperation::create([
            'width' => 600,
            'height' => 480,
        ]);

        $this->beConstructedWith([$colorspaceApplier, $thumbnailApplier]);
        $colorspaceApplier->supports($operation)->willReturn(false);
        $thumbnailApplier->supports($operation)->willReturn(false);

        $this->shouldThrow(TransformationException::class)->during('getApplier', [$operation]);
    }
}
