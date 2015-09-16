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

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
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
     * @param SerializerInterface $serializer
     * @param LocaleManager       $localeManager
     * @param NormalizerInterface $channelNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        NormalizerInterface $channelNormalizer
    ) {
        parent::__construct($serializer, $localeManager);
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
