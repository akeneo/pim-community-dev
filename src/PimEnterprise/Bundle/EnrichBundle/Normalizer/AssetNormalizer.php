<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['structured'];

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
        $normalizedData = $this->assetNormalizer->normalize($asset, $format, $context);
        $normalizedData['references'] = $this->normalizeReferences($asset->getReferences());
        $normalizedData['categories'] = array_map(
            function (CategoryInterface $category) {
                return $category->getCode();
            },
            $asset->getCategories()->toArray()
        );

        return $normalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssetInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the given references
     *
     * @param ArrayCollection $references
     *
     * @return array
     */
    protected function normalizeReferences(ArrayCollection $references)
    {
        $normalizedReferences = [];
        foreach ($references as $reference) {
            $normalizedReferences[] = [
                'locale' => (null !== $reference->getLocale()) ? $reference->getLocale()->getCode() : null,
                'file'   => (null !== $reference->getFile()) ? $reference->getFile()->getKey() : null
            ];
        }

        return $normalizedReferences;
    }
}
