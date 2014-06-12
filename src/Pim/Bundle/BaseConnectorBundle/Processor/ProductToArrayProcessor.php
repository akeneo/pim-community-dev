<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

/**
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
     * @var string Channel code
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var array
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
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $this->flatProductNormalizer->normalize($item, null, $this->getNormalizerContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                )
            )
        );
    }

    /**
     * Set channel
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     *
     * @return string
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
    protected function getNormalizercontext()
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
