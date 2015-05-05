<?php

namespace Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Pim\Bundle\EnrichBundle\Exception\ChannelNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Process a product to a flat array
 *
 * This processor doesn't use the channel in configuration field but from job configuration
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends AbstractMassEditProcessor
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $channelCode;

    /** @var array Normalizer context */
    protected $normalizerContext;

    /**
     * @param MassEditRepositoryInterface $massEditRepository
     * @param SerializerInterface         $serializer
     * @param ChannelManager              $channelManager
     * @param string                      $uploadDirectory
     */
    public function __construct(
        MassEditRepositoryInterface $massEditRepository,
        SerializerInterface $serializer,
        ChannelManager $channelManager,
        $uploadDirectory
    ) {
        parent::__construct($massEditRepository);

        $this->serializer      = $serializer;
        $this->channelManager  = $channelManager;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->setChannelCodeFromJobConfiguration();
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
     * @param string $channelCode
     */
    public function setChannelCode($channelCode)
    {
        $this->channelCode = $channelCode;
    }

    /**
     * @return string
     */
    public function getChannelCode()
    {
        return $this->channelCode;
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
                'scopeCode'   => $this->channelCode,
                'localeCodes' => $this->getLocaleCodes($this->channelCode)
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
        $media = [];
        foreach ($product->getValues() as $value) {
            if (in_array($value->getAttribute()->getAttributeType(), ['pim_catalog_image', 'pim_catalog_file'])) {
                $media[] = $value->getData();
            }
        }

        return $media;
    }

    /**
     * Set the channel in parameter from the job configuration
     *
     * @throws ChannelNotFoundException
     */
    protected function setChannelCodeFromJobConfiguration()
    {
        $configuration = $this->getJobConfiguration();

        if (!isset($configuration['mainContext']['scope'])) {
            throw new ChannelNotFoundException();
        }
        $this->setChannelCode($configuration['mainContext']['scope']);
    }
}
