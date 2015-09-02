<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer\Flat;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var NormalizerInterface */
    protected $assetNormalizer;

    /**
     * @param NormalizerInterface $assetNormalizer
     */
    public function __construct(NormalizerInterface $assetNormalizer)
    {
        $this->assetNormalizer = $assetNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($asset, $format = null, array $context = [])
    {
        if (array_key_exists('field_name', $context)) {
            return [
                $context['field_name'] => $asset->getCode(),
            ];
        }

        $normalizedData               = $this->assetNormalizer->normalize($asset, $format, $context);
        $normalizedData['tags']       = $asset->getTagCodes();
        $normalizedData['categories'] = $asset->getCategoryCodes();

        if (array_key_exists('versioning', $context)) {
            $normalizedData['references'] = array_filter(array_map(function ($reference) {
                return null !== $reference->getFileInfo() ? $reference->getFileInfo()->getKey() : null;
            }, $asset->getReferences()->toArray()));

            $normalizedData['variations'] = array_filter(array_map(function ($variation) {
                return null !== $variation->getFileInfo() ? $variation->getFileInfo()->getKey() : null;
            }, $asset->getVariations()));
        }

        return $normalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssetInterface && in_array($format, $this->supportedFormats);
    }
}
