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

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Processes and transforms ChannelVariationsConfiguration to an array of channel configuration
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ChannelConfigurationProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $channelNormalizer;

    /**
     * @param NormalizerInterface $channelNormalizer
     */
    public function __construct(NormalizerInterface $channelNormalizer)
    {
        $this->channelNormalizer = $channelNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($channelConf)
    {
        return $this->channelNormalizer->normalize($channelConf);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
