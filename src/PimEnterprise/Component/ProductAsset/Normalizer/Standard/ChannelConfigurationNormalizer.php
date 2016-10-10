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

use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ChannelConfigurationNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($channelConf, $format = null, array $context = [])
    {
        return [
            'channel'       => $channelConf->getChannel()->getCode(),
            'configuration' => $channelConf->getConfiguration()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelVariationsConfigurationInterface && 'standard' === $format;
    }
}
