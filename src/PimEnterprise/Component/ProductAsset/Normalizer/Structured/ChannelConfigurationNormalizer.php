<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer\Structured;

use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ChannelConfigurationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['structured'];

    /**
     * {@inheritdoc}
     */
    public function normalize($channelConf, $format = null, array $context = [])
    {
        $normalizedData['channel'] = $channelConf->getChannel()->getCode();
        $normalizedData['configuration'] = $channelConf->getConfiguration();

        return $normalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelVariationsConfigurationInterface && in_array($format, $this->supportedFormats);
    }
}
