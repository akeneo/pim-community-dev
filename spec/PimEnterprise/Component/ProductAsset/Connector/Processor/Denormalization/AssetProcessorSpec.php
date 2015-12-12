<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $assetConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $assetUpdater,
        AssetFactory $assetFactory,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($assetConverter, $repository, $assetUpdater, $assetFactory, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_updates_an_existing_asset(
        $assetConverter,
        $repository,
        $assetUpdater,
        $validator,
        AssetInterface $asset,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($asset);

        $asset->getId()->willReturn(42);

        $values = $this->getValues();

        $assetConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        unset($values['converted_values']['localized']);

        $assetUpdater
            ->update($asset, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($asset)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($asset);
    }

    function it_skips_a_asset_when_update_fails(
        $assetConverter,
        $repository,
        $assetUpdater,
        $validator,
        AssetInterface $asset,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($asset);

        $asset->getId()->willReturn(42);

        $values = $this->getValues();

        $assetConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        unset($values['converted_values']['localized']);

        $assetUpdater
            ->update($asset, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $validator
            ->validate($asset)
            ->willReturn($violationList);

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_asset_when_object_is_invalid(
        $assetConverter,
        $repository,
        $assetUpdater,
        $validator,
        AssetInterface $asset,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($asset);

        $asset->getId()->willReturn(42);

        $values = $this->getValues();

        $assetConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        unset($values['converted_values']['localized']);

        $assetUpdater
            ->update($asset, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($asset)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($asset);

        $assetUpdater
            ->update($asset, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($asset)
            ->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function getValues()
    {
        return [
            'original_values' => [
                'code'        => 'mycode',
                'localized'   => 0,
                'description' => 'My awesome description',
                'tags'        => 'dog,flowers',
                'end_of_use'  => '2018/02/01',
            ],
            'converted_values' => [
                'code'        => 'mycode',
                'localized'   => false,
                'description' => 'My awesome description',
                'tags'        => ['dog', 'flowers'],
                'end_of_use'  => '2018-02-01',
            ],
        ];
    }
}
