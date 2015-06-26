<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer;

use Doctrine\Common\Collections\Collection;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['structured'];

    /**
     * {@inheritdoc}
     */
    public function normalize($asset, $format = null, array $context = [])
    {
        return [
            'code'          => $asset->getCode(),
            'categories'    => array_map(function (CategoryInterface $category) {
                return $category->getCode();
            }, $asset->getCategories()->toArray()),
            'description'   => $asset->getDescription(),
            'references'    => $this->normalizeReferences($asset->getReferences()),
            'enabled'       => $asset->isEnabled(),
            'end_of_use_at' => null !== $asset->getEndOfUseAt() ? $asset->getEndOfUseAt()->format('Y-m-d H:i:s') : null,
            'created_at'    => null !== $asset->getCreatedAt() ? $asset->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updated_at'    => null !== $asset->getUpdatedAt() ? $asset->getUpdatedAt()->format('Y-m-d H:i:s') : null,

        ];
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
        $normalizeedReferences = [];
        foreach ($references as $reference) {
            $normalizeedReferences[] = [
                'locale' => null !== $reference->getLocale() ? $reference->getLocale()->getCode() : null,
                'file'   => null !== $reference->getFile() ? $reference->getFile()->getKey() : null
            ];
        }

        return $normalizeedReferences;
    }
}
