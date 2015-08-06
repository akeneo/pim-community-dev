<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\DummyProcessor;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Processes and transforms assets to array of assets
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetProcessor extends DummyProcessor
{
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
    public function process($asset)
    {
        $normalizedAsset = $this->assetNormalizer->normalize($asset);
        unset($normalizedAsset['references']);

        return $normalizedAsset;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
