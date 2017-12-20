<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $datetimeNormalizer;

    /**
     * @param NormalizerInterface $datetimeNormalizer
     */
    public function __construct(NormalizerInterface $datetimeNormalizer)
    {
        $this->datetimeNormalizer = $datetimeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($asset, $format = null, array $context = [])
    {
        return [
            'code'        => $asset->getCode(),
            'localizable' => (bool) $asset->isLocalizable(),
            'description' => '' === $asset->getDescription() ? null : $asset->getDescription(),
            'end_of_use'  => $this->datetimeNormalizer->normalize($asset->getEndOfUseAt(), 'standard', $context),
            'tags'        => $asset->getTagCodes(),
            'categories'  => $asset->getCategoryCodes(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssetInterface && 'standard' === $format;
    }
}
