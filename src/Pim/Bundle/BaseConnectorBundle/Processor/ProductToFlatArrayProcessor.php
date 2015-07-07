<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
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
    /** @var Serializer */
    protected $serializer;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     *
     * @var string $channel Channel code
     */
    protected $channel;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var array Normalizer context */
    protected $normalizerContext;

    /** @var string */
    protected $uploadDirectory;

    /**
     * @param Serializer     $serializer
     * @param ChannelManager $channelManager
     * @param string         $uploadDirectory
     */
    public function __construct(
        Serializer $serializer,
        ChannelManager $channelManager,
        $uploadDirectory
    ) {
        $this->serializer      = $serializer;
        $this->channelManager  = $channelManager;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $data['media'] = [];
        $productMedias = $this->getProductMedias($product);
        if (count($productMedias) > 0) {
            try {
                $data['media'] = $this->serializer->normalize(
                    $productMedias,
                    'flat',
                    ['field_name' => 'media', 'prepare_copy' => true]
                );
            } catch (FileNotFoundException $e) {
                throw new InvalidItemException(
                    $e->getMessage(),
                    [
                        'item'            => $product->getIdentifier()->getData(),
                        'uploadDirectory' => $this->uploadDirectory,
                    ]
                );
            }
        }

        $data['product'] = $this->serializer->normalize($product, 'flat', $this->getNormalizerContext());

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

    /**
     * Fetch product medias
     *
     * @param ProductInterface $product
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductMediaInterface[]
     */
    protected function getProductMedias(ProductInterface $product)
    {
        $media = array();
        foreach ($product->getValues() as $value) {
            if (in_array(
                $value->getAttribute()->getAttributeType(),
                array('pim_catalog_image', 'pim_catalog_file')
            )) {
                $media[] = $value->getData();
            }
        }

        return $media;
    }
}
