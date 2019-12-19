<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Normalizer\InternalApi;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\Reference;
use Akeneo\Asset\Component\Model\Variation;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

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
        $normalizedData = $this->assetNormalizer->normalize($asset, 'standard', $context);
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
     * @param Collection $references
     *
     * @return array
     */
    protected function normalizeReferences(Collection $references)
    {
        $normalizedReferences = [];
        foreach ($references as $reference) {
            $normalizedReferences[] = [
                'locale' => (null !== $reference->getLocale()) ? $reference->getLocale()->getCode() : null,
                'file' => (null !== $reference->getFileInfo()) ? $reference->getFileInfo()->getKey() : null,
                'has_variations' => $this->countValidVariations($reference) > 0,
            ];
        }

        return $normalizedReferences;
    }

    /**
     * Count the variations where there is a file
     *
     * @param Reference $reference
     *
     * @return int
     */
    private function countValidVariations(Reference $reference): int
    {
        return array_reduce(
            $reference->getVariations()->toArray(),
            function (int $total, Variation $variation) {
                return null !== $variation->getFileInfo() ? $total + 1 : $total;
            },
            0
        );
    }
}
