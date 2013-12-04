<?php

namespace Pim\Bundle\ImportExportBundle\Processor\JSONNormalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;


class ProductProcessor extends JSONNormalizer
{

    /**
     * @Assert\NotBlank
     * @Channel
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @param NormalizerInterface $normalizer
     * @param ChannelManager      $channelManager
     */
    public function __construct(NormalizerInterface $normalizer, ChannelManager $channelManager)
    {
        parent::__construct($normalizer);

        $this->channelManager = $channelManager;
    }

    /**
     * Set channel
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }


    /**
     * {@inheritdoc}
     */
    public function process($products)
    {
        $this->setNormalizerChannel();
        $productsArray = parent::process($products);
        
        if (!is_array($products)) {
            $products = array($products);
        }

        $media = array();
        foreach ($products as $product) {
            $media = array_merge($product->getMedia(), $media);
        }

        return array(
            'products' => $productsArray,
            'media' => $media
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(), array(
            'channel' => array(
                'type' => 'choice',
                'options' => array(
                    'choices' => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            )
                )
        );
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
    
    protected function setNormalizerChannel(){
        $this->normalizer->setChannel($this->getChannelEntity());
    }

    /**
     * Get channel
     * @return string
     */
    protected function getChannelEntity()
    {
        $channel = $this->channelManager->getChannelByCode($this->channel);
        return $channel ? $channel : null;
    }
}
