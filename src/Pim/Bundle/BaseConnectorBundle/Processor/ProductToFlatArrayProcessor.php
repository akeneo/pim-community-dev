<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Process a product to an array
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

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
     * @param Serializer     $serializer
     * @param ChannelManager $channelManager
     */
    public function __construct(
        Serializer $serializer,
        ChannelManager $channelManager
    ) {
        $this->serializer     = $serializer;
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data['media'] = [];
        if (count($item->getMedia()) > 0) {
            $data['media'] = $this->serializer->normalize(
                $item->getMedia(),
                'flat',
                ['field_name' => 'media', 'prepare_copy' => true]
            );
        }

        $data['product'] = $this->serializer->normalize($item, 'flat', $this->getNormalizerContext());

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
