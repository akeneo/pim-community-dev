<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Asset\Component\Upload\MassUpload;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use Akeneo\Asset\Component\Upload\MassUpload\AddAssetToEntityWithValues;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddAssetToEntityWithValuesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $entityWithValueRepository,
        ObjectUpdaterInterface $entityWithValueUpdater,
        ValidatorInterface $validator,
        SaverInterface $entityWithValueSaver
    ) {
        $this->beConstructedWith($entityWithValueRepository, $entityWithValueUpdater, $validator, $entityWithValueSaver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddAssetToEntityWithValues::class);
    }

    function it_adds_assets_to_an_asset_collection(
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        EntityWithValuesInterface $entityWithValues,
        ReferenceDataCollectionValueInterface $previousValue,
        AssetInterface $assetC,
        ConstraintViolationListInterface $violations
    ) {
        $entityWithValueRepository->findOneByIdentifier('foobar')->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([$assetC]);
        $assetC->getCode()->willReturn('asset_c');

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_collection' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_c', 'asset_a', 'asset_b'],
                ]],
            ],
        ])->shouldBeCalled();

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->add('foobar', 'asset_collection', ['asset_a', 'asset_b']);
    }

    function it_adds_assets_to_an_empty_asset_collection(
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        EntityWithValuesInterface $entityWithValues,
        ReferenceDataCollectionValueInterface $previousValue,
        ConstraintViolationListInterface $violations
    ) {
        $entityWithValueRepository->findOneByIdentifier('foobar')->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([]);

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_collection' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_a', 'asset_b'],
                ]],
            ],
        ])->shouldBeCalled();

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->add('foobar', 'asset_collection', ['asset_a', 'asset_b']);
    }

    function it_adds_assets_to_a_new_asset_collection(
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        EntityWithValuesInterface $entityWithValues,
        ConstraintViolationListInterface $violations
    ) {
        $entityWithValueRepository->findOneByIdentifier('foobar')->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn(null);

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_collection' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_a', 'asset_b'],
                ]],
            ],
        ])->shouldBeCalled();

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->add('foobar', 'asset_collection', ['asset_a', 'asset_b']);
    }

    function it_does_not_update_the_asset_collection_of_an_invalid_product(
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        EntityWithValuesInterface $entityWithValues,
        ReferenceDataCollectionValueInterface $previousValue,
        AssetInterface $assetC,
        ConstraintViolationList $violations,
        \IteratorAggregate $violationIteratorAggregate,
        \Iterator $violationIterator,
        ConstraintViolationInterface $violation1,
        ConstraintViolationInterface $violation2
    ) {
        $entityWithValueRepository->findOneByIdentifier('foobar')->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([$assetC]);
        $assetC->getCode()->willReturn('asset_c');

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_collection' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_c', 'asset_a', 'asset_b'],
                ]],
            ],
        ])->shouldBeCalled();

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(1);
        $violations->getIterator()->willReturn($violationIteratorAggregate);
        $violationIteratorAggregate->getIterator()->willReturn($violationIterator);
        $violationIterator->rewind()->shouldBeCalled();
        $violationIterator->valid()->willReturn(true, true, false);
        $violationIterator->current()->willReturn($violation1, $violation2);
        $violationIterator->next()->shouldBeCalled();

        $violation1->getMessage()->willReturn('First product violation');
        $violation2->getMessage()->willReturn('Second product violation');

        $message = 'First product violation' . PHP_EOL . 'Second product violation' . PHP_EOL;
        $this
            ->shouldThrow(new \InvalidArgumentException($message))
            ->during('add', ['foobar', 'asset_collection', ['asset_a', 'asset_b']]);

        $entityWithValueSaver->save(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_does_not_add_assets_to_an_asset_collection_if_product_does_not_exist(
        $entityWithValueRepository,
        $entityWithValueSaver
    ) {
        $entityWithValueRepository->findOneByIdentifier('foobar')->willReturn(null);

        $this->shouldThrow(
            new \InvalidArgumentException('Product or product model with identifier "foobar" does not exist.')
        )->during('add', ['foobar', 'asset_collection', ['asset_a', 'asset_b']]);

        $entityWithValueSaver->save(Argument::any())->shouldNotHaveBeenCalled();
    }
}
