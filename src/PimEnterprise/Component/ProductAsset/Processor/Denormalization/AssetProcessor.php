<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Bundle\ProductAssetBundle\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Asset import processor, allows to,
 *  - create / update asset
 *  - return the valid asset, throw exceptions to skip invalid ones
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $assetConverter;

    /** @var ObjectUpdaterInterface */
    protected $assetUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AssetFactory */
    protected $assetFactory;

    /**
     * @param StandardArrayConverterInterface       $assetConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $assetUpdater
     * @param ValidatorInterface                    $validator
     * @param AssetFactory                          $assetFactory
     */
    public function __construct(
        StandardArrayConverterInterface $assetConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $assetUpdater,
        AssetFactory $assetFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->assetConverter = $assetConverter;
        $this->assetUpdater   = $assetUpdater;
        $this->assetFactory   = $assetFactory;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        try {
            $convertedItem = $this->convertItemData($item);
            $asset = $this->findOrCreateAsset($convertedItem);
            $this->updateAsset($asset, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            return;
        }

        return $asset;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->assetConverter->convert($item);
    }

    /**
     * Find or create the asset
     *
     * @param array $convertedItem
     *
     * @return AssetInterface
     */
    protected function findOrCreateAsset(array $convertedItem)
    {
        $asset = $this->findObject($this->repository, $convertedItem);
        if (null === $asset) {
            $asset = $this->assetFactory->create($convertedItem['localized']);
        }

        return $asset;
    }

    /**
     * Update the asset fields
     *
     * @param AssetInterface $asset
     * @param array          $convertedItem
     */
    protected function updateAsset(AssetInterface $asset, array $convertedItem)
    {
        $this->assetUpdater->update($asset, $convertedItem);
    }

    /**
     * @param AssetInterface $asset
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateAsset(AssetInterface $asset)
    {
        $violations = $this->validator->validate($asset);

        return $violations;
    }
}
