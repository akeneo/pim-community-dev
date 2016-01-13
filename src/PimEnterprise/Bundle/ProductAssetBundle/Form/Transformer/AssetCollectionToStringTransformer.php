<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Transformer;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Ajax asset tag transformer
 *
 * @author Julien sanchez <julien@akeneo.com>
 */
class AssetCollectionToStringTransformer implements DataTransformerInterface
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return [];
        }

        return $this->assetRepository->findByIdentifiers(explode(',', $value));
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        $assetCodes = [];
        foreach ($value as $asset) {
            if (!($asset instanceof AssetInterface)) {
                throw new TransformationFailedException();
            }

            $assetCodes[] = $asset->getCode();
        }

        return implode(',', $assetCodes);
    }
}
