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

namespace spec\PimEnterprise\Component\ProductAsset\Upload\MassUpload;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Upload\MassUpload\AddAssetToEntityWithValues;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddAssetToEntityWithValuesSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $entityWithValueRepository,
        ObjectUpdaterInterface $entityWithValueUpdater,
        ValidatorInterface $validator,
        SaverInterface $entityWithValueSaver
    ) {
        $this->beConstructedWith($entityWithValueRepository, $entityWithValueUpdater, $validator,$entityWithValueSaver);
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
        $entityWithValueRepository->find(42)->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([$assetC]);
        $assetC->getCode()->willReturn('asset_c');

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_code' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_c', 'asset_a', 'asset_b'],
                ]],
            ],
        ]);

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->add(42, 'asset_collection', ['asset_a', 'asset_b']);
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
        $entityWithValueRepository->find(42)->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([]);

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_code' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_a', 'asset_b'],
                ]],
            ],
        ]);

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(0);

        $entityWithValueSaver->save($entityWithValues)->shouldBeCalled();

        $this->add(42, 'asset_collection', ['asset_a', 'asset_b']);
    }

    function it_does_not_update_the_asset_collection_of_an_invalid_product(
        $entityWithValueRepository,
        $entityWithValueUpdater,
        $validator,
        $entityWithValueSaver,
        EntityWithValuesInterface $entityWithValues,
        ReferenceDataCollectionValueInterface $previousValue,
        AssetInterface $assetC,
        ConstraintViolationListInterface $violations
    ) {
        $entityWithValueRepository->find(42)->willReturn($entityWithValues);
        $entityWithValues->getValue('asset_collection')->willReturn($previousValue);
        $previousValue->getData()->willReturn([$assetC]);
        $assetC->getCode()->willReturn('asset_c');

        $entityWithValueUpdater->update($entityWithValues, [
            'values' => [
                'asset_code' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => ['asset_c', 'asset_a', 'asset_b'],
                ]],
            ],
        ]);

        $validator->validate($entityWithValues)->willReturn($violations);
        $violations->count()->willReturn(1);

        $entityWithValueSaver->save($entityWithValues)->shouldNotBeCalled();

        $this->add(42, 'asset_collection', ['asset_a', 'asset_b']);
    }
}
