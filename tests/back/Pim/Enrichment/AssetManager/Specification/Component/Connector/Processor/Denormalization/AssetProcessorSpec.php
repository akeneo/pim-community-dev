<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Denormalization;

use Akeneo\AssetManager\Application\Asset\CreateAndEditAsset\CreateAndEditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Connector\EditAssetCommandFactory as NamingConventionEditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindMediaFileAttributeCodesInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Processor\Denormalization\AssetProcessor;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssetProcessorSpec extends ObjectBehavior
{
    public function let(
        EditAssetCommandFactory $editAssetCommandFactory,
        AssetExistsInterface $assetExists,
        ValidatorInterface $validator,
        FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes,
        FileStorerInterface $fileStorer,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $editAssetCommandFactory,
            $assetExists,
            $validator,
            $findMediaFileAttributeCodes,
            $fileStorer,
            $namingConventionEditAssetCommandFactory
        );
        $stepExecution->getExecutionContext()->willReturn(new ExecutionContext());
        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_is_a_denormalization_asset_processor()
    {
        $this->shouldHaveType(AssetProcessor::class);
    }

    public function it_throws_an_exception_if_item_is_not_an_valid_normalized_asset()
    {
        $normalizedAssetWithoutAssetFamilyIdentifier = ['invalid_family' => 'invalid'];
        $normalizedAssetWithoutCode = ['asset_family_identifier' => 'packshot'];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('process', [$normalizedAssetWithoutAssetFamilyIdentifier]);
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('process', [$normalizedAssetWithoutCode]);
    }

    public function it_applies_naming_convention(
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        ValidatorInterface $validator
    ) {
        $normalizedAsset = [
            'code' => 'an_asset',
            'asset_family_identifier' => 'packshot',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAsset['asset_family_identifier']);
        $assetCode = AssetCode::fromString($normalizedAsset['code']);

        $assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn(true);

        $editAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, []);
        $nameConventionEditAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'custom_value',
        ]);

        $editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset)->willReturn($editAssetCommand);
        $namingConventionEditAssetCommandFactory->create($normalizedAsset, $assetFamilyIdentifier)->willReturn($nameConventionEditAssetCommand);

        $expectedEditAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'custom_value',
        ]);

        $validator->validate($expectedEditAssetCommand)->willReturn(new ConstraintViolationList([]));

        $expectedCreateAndEditAssetCommand = new CreateAndEditAssetCommand(null, $expectedEditAssetCommand);

        $this->process($normalizedAsset)->shouldBeLike($expectedCreateAndEditAssetCommand);
    }

    public function it_overrides_imported_values_by_naming_convention_values(
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        ValidatorInterface $validator
    ) {
        $normalizedAsset = [
            'code' => 'an_asset',
            'asset_family_identifier' => 'packshot',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAsset['asset_family_identifier']);
        $assetCode = AssetCode::fromString($normalizedAsset['code']);

        $assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn(true);

        $editAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'imported_custom_value'
        ]);
        $nameConventionEditAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'naming_convention_custom_value',
        ]);

        $editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset)->willReturn($editAssetCommand);
        $namingConventionEditAssetCommandFactory->create($normalizedAsset, $assetFamilyIdentifier)->willReturn($nameConventionEditAssetCommand);

        $expectedEditAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'naming_convention_custom_value',
        ]);

        $validator->validate($expectedEditAssetCommand)->willReturn(new ConstraintViolationList([]));

        $expectedCreateAndEditAssetCommand = new CreateAndEditAssetCommand(null, $expectedEditAssetCommand);

        $this->process($normalizedAsset)->shouldBeLike($expectedCreateAndEditAssetCommand);
    }

    public function it_handle_naming_convention_exception_with_error(
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        StepExecution $stepExecution
    ) {
        $normalizedAsset = [
            'code' => 'an_asset',
            'asset_family_identifier' => 'packshot',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAsset['asset_family_identifier']);
        $assetCode = AssetCode::fromString($normalizedAsset['code']);

        $assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn(true);

        $editAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'imported_custom_value'
        ]);

        $editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset)->willReturn($editAssetCommand);
        $namingConventionExceptionMessage = 'Naming convention exception';
        $namingConventionException = new NamingConventionException(new \Exception($namingConventionExceptionMessage), true);
        $namingConventionEditAssetCommandFactory->create($normalizedAsset, $assetFamilyIdentifier)->willThrow($namingConventionException);

        $itemPosition = 0;
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->willReturn($itemPosition);

//        $invalidItemException = new InvalidItemException(
//            $namingConventionExceptionMessage,
//            new FileInvalidItem($normalizedAsset, $itemPosition),
//            [],
//            0,
//            $namingConventionException
//        );

//        $this->shouldThrow($invalidItemException)->during('process', [$normalizedAsset]);
        $this->shouldThrow(InvalidItemException::class)->during('process', [$normalizedAsset]);
    }

    public function it_handle_naming_convention_exception_without_error(
        AssetExistsInterface $assetExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        NamingConventionEditAssetCommandFactory $namingConventionEditAssetCommandFactory,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $normalizedAsset = [
            'code' => 'an_asset',
            'asset_family_identifier' => 'packshot',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($normalizedAsset['asset_family_identifier']);
        $assetCode = AssetCode::fromString($normalizedAsset['code']);

        $assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn(true);

        $editAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'imported_custom_value'
        ]);

        $editAssetCommandFactory->create($assetFamilyIdentifier, $normalizedAsset)->willReturn($editAssetCommand);
        $namingConventionExceptionMessage = 'Naming convention exception';
        $namingConventionException = new NamingConventionException(new \Exception($namingConventionExceptionMessage), false);
        $namingConventionEditAssetCommandFactory->create($normalizedAsset, $assetFamilyIdentifier)->willThrow($namingConventionException);

        $expectedEditAssetCommand = new EditAssetCommand($assetFamilyIdentifier, $assetCode, [
            'custom_attribute' => 'imported_custom_value',
        ]);

        $validator->validate($expectedEditAssetCommand)->willReturn(new ConstraintViolationList([]));

        $expectedCreateAndEditAssetCommand = new CreateAndEditAssetCommand(null, $expectedEditAssetCommand);

        $this->process($normalizedAsset)->shouldBeLike($expectedCreateAndEditAssetCommand);
        $this->flushNonBlockingWarnings()->shouldBeLike([
            new Warning(
                $stepExecution->getWrappedObject(),
                'Naming convention was not applied due to the following reason: "%error_message%"',
                ['%error_message%' => $namingConventionExceptionMessage],
                $normalizedAsset
            )
        ]);
    }

    //TODO RAC-1075: add tests for complete use case
}
