<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Process a product to an array
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var Serializer */
    protected $serializer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param Serializer                 $serializer
     * @param ChannelRepositoryInterface $channelRepository
     * @param ProductBuilderInterface    $productBuilder
     * @param string[]                   $mediaAttributeTypes
     */
    public function __construct(
        Serializer $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        array $mediaAttributeTypes
    ) {
        $this->serializer          = $serializer;
        $this->channelRepository   = $channelRepository;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->productBuilder      = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $channelCode = $parameters->get('channel');
        $contextChannel = $this->channelRepository->findOneByIdentifier($channelCode);
        $this->productBuilder->addMissingProductValues(
            $product,
            [$contextChannel],
            $contextChannel->getLocales()->toArray()
        );

        $data['media'] = [];
        $mediaValues   = $this->getMediaProductValues($product);

        foreach ($mediaValues as $mediaValue) {
            $data['media'][] = $this->serializer->normalize(
                $mediaValue->getMedia(),
                'flat',
                ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
            );
        }

        $data['product'] = $this->serializer->normalize($product, 'flat', $this->getNormalizerContext($contextChannel));

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Get normalizer context
     *
     * @param ChannelInterface $channel
     *
     * @return array $normalizerContext
     */
    protected function getNormalizerContext(ChannelInterface $channel)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $decimalSeparator = $parameters->get('decimalSeparator');
        $dateFormat = $parameters->get('dateFormat');

        $normalizerContext = [
            'scopeCode'         => $channel->getCode(),
            'localeCodes'       => $channel->getLocaleCodes(),
            'decimal_separator' => $decimalSeparator,
            'date_format'       => $dateFormat,
        ];

        return $normalizerContext;
    }

    /**
     * Fetch medias product values
     *
     * @param ProductInterface $product
     *
     * @return ProductValueInterface[]
     */
    protected function getMediaProductValues(ProductInterface $product)
    {
        $values = [];
        foreach ($product->getValues() as $value) {
            if (in_array(
                $value->getAttribute()->getAttributeType(),
                $this->mediaAttributeTypes
            )) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
