<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;

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

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param SerializerInterface        $serializer
     * @param LocaleRepositoryInterface  $localeRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->localeRepository  = $localeRepository;
        $this->serializer        = $serializer;
        $this->channelRepository = $channelRepository;
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
     * {@inheritdoc}
     */
    public function process($products)
    {
        $nbItems = count($products) - ($this->isWithHeader() ? 1 : 0);
        $this->stepExecution->addSummaryInfo('write', $nbItems);

        $csv =  $this->serializer->serialize(
            $products,
            'csv',
            [
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => true,
                'scopeCode'     => $this->channel,
                'localeCodes'   => $this->getLocaleCodes($this->channel)
            ]
        );

        if (!is_array($products)) {
            $products = [$products];
        }

        $media = [];
        foreach ($products as $product) {
            $media = array_merge($this->getProductMedia($product), $media);
        }

        return [
            'entry' => $csv,
            'media' => $media
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => $this->channelRepository->getLabelsIndexedByCode(),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ]
            ]
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
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);

        return $channel->getLocaleCodes();
    }


    /**
     * Get all the media of the product
     *
     * @param ProductInterface $product
     *
     * @return FileInfoInterface[]
     */
    public function getProductMedia(ProductInterface $product)
    {
        $media = [];
        foreach ($product->getValues() as $value) {
            if (in_array(
                $value->getAttribute()->getAttributeType(),
                [AttributeTypes::IMAGE, AttributeTypes::FILE]
            )) {
                $media[] = $value->getData();
            }
        }

        return $media;
    }
}
