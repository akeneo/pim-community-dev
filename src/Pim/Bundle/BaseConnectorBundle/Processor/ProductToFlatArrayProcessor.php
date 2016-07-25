<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;
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

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param Serializer                   $serializer
     * @param ChannelRepositoryInterface   $channelRepository
     * @param ProductBuilderInterface      $productBuilder
     * @param ObjectDetacherInterface      $detacher
     * @param FieldSplitter                $fieldSplitter
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string[]                     $mediaAttributeTypes
     */
    public function __construct(
        Serializer $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $detacher,
        FieldSplitter $fieldSplitter,
        AttributeRepositoryInterface $attributeRepository,
        array $mediaAttributeTypes
    ) {
        $this->serializer          = $serializer;
        $this->channelRepository   = $channelRepository;
        $this->productBuilder      = $productBuilder;
        $this->detacher            = $detacher;
        $this->fieldSplitter       = $fieldSplitter;
        $this->attributeRepository = $attributeRepository;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $scopeCode  = $parameters->get('filters')['structure']['scope'];
        $contextChannel = $this->channelRepository->findOneByIdentifier($scopeCode);
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

        $attributes = $this->getAttributesToFilter();
        if (null !== $attributes) {
            $data['product'] = $this->filterAttributes(
                $product,
                $data['product'],
                $attributes
            );
        }

        $this->detacher->detach($product);

        return $data;
    }

    /**
     * Return a list of attributes to export
     *
     * @return array|null
     */
    protected function getAttributesToFilter()
    {
        $attributes = null;
        $parameters = $this->stepExecution->getJobParameters();

        if (isset($parameters->get('filters')['structure']['attributes'])) {
            $attributes = $parameters->get('filters')['structure']['attributes'];
            $identifierCode = $this->attributeRepository->getIdentifierCode();
            if (!in_array($identifierCode, $attributes)) {
                $attributes[] = $identifierCode;
            }
        }

        return $attributes;
    }

    /**
     * Filters the attributes that have to be exported based on a product and a list of attributes
     *
     * @param ProductInterface $product
     * @param array            $normalizedProduct
     * @param array            $attributes
     *
     * @return array
     */
    protected function filterAttributes(
        ProductInterface $product,
        array $normalizedProduct,
        array $attributes
    ) {
        $filteredColumnList = [];

        foreach (array_keys($normalizedProduct) as $flatField) {
            $fieldParts = $this->fieldSplitter->splitFieldName($flatField);
            $attribute = $this->getAttributeByCode($product, $fieldParts[0]);

            if (null === $attribute || in_array($attribute->getCode(), $attributes)) {
                $filteredColumnList[] = $flatField;
            }
        }

        return array_intersect_key($normalizedProduct, array_flip($filteredColumnList));
    }

    /**
     * @param ProductInterface $product
     *
     * @return AttributeInterface|null
     */
    protected function getAttributeByCode(ProductInterface $product, $attributeCode)
    {
        foreach ($product->getValues() as $value) {
            if ($value->getAttribute()->getCode() === $attributeCode) {
                return $value->getAttribute();
            }
        }

        return null;
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
            'localeCodes'       => array_intersect(
                $channel->getLocaleCodes(),
                $parameters->get('filters')['structure']['locales']
            ),
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
