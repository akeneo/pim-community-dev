<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms ChannelVariationsConfiguration to an array of channel configuration
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ChannelConfigurationProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $channelNormalizer;

    /**
     * @param SerializerInterface       $serializer
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $channelNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $channelNormalizer
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->channelNormalizer = $channelNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($channelConf)
    {
        return $normalizedChannels = $this->channelNormalizer->normalize($channelConf);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
