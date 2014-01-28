<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Product serializer into csv processor
 *
 * This processor stores the media of the products among
 * with the serialized products in order to write them later
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends HeterogeneousProcessor
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     */
    protected $channel;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @param SerializerInterface $serializer
     * @param LocaleManager       $localeManager
     * @param ChannelManager      $channelManager
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        ChannelManager $channelManager
    ) {
        parent::__construct($serializer, $localeManager);

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
        $nbItems = count($products) - ($this->isWithHeader() ? 1 : 0);
        $this->stepExecution->addSummaryInfo('write', $nbItems);

        $csv =  $this->serializer->serialize(
            $products,
            'csv',
            array(
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => true,
                'scopeCode'     => $this->channel,
                'localeCodes'   => $this->getLocaleCodes($this->channel)
            )
        );

        if (!is_array($products)) {
            $products = array($products);
        }

        $media = array();
        foreach ($products as $product) {
            $media = array_merge($product->getMedia(), $media);
        }

        return array(
            'entry' => $csv,
            'media' => $media
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_import_export.export.channel.label',
                        'help'     => 'pim_import_export.export.channel.help'
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
}
