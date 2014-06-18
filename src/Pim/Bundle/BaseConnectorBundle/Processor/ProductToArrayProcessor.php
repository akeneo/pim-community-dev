<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

/**
 * Process a product to an array
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @var FlatProductNormalizer
     */
    protected $flatProductNormalizer;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     *
     * @var string $channel Channel code
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var array Normalizer context
     */
    protected $normalizerContext;

    /**
     * @param FlatProductNormalizer $flatProductNormalizer
     * @param ChannelManager        $channelManager
     */
    public function __construct(
        FlatProductNormalizer $flatProductNormalizer,
        ChannelManager $channelManager
    ) {
        $this->flatProductNormalizer = $flatProductNormalizer;
        $this->channelManager        = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data['media']   = $item->getMedia();
        $data['product'] = $this->flatProductNormalizer->normalize($item, null, $this->getNormalizerContext());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];
    }

    /**
     * Set channel
     *
     * @param string $channelCode Channel code
     *
     * @return $this
     */
    public function setChannel($channelCode)
    {
        $this->channel = $channelCode;

        return $this;
    }

    /**
     * Get channel
     *
     * @return string Channel code
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get normalizer context
     *
     * @return array $normalizerContext
     */
    protected function getNormalizerContext()
    {
        if (null === $this->normalizerContext) {
            $this->normalizerContext = [
                'scopeCode'   => $this->channel,
                'localeCodes' => $this->getLocaleCodes($this->channel)
            ];
        }

        return $this->normalizerContext;
    }

    /**
     * Get locale codes for a channel
     *
     * @param string $channelCode
     *
     * @return array
     */
    protected function getLocaleCodes($channelCode)
    {
        $channel = $this->channelManager->getChannelByCode($channelCode);

        return $channel->getLocaleCodes();
    }
}
