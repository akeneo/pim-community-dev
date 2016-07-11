<?php

namespace Pim\Component\Connector\Processor\Normalization;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ObjectInvalidItem;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Variant group export processor, allows to,
 *  - normalize variant groups and related values (media included)
 *  - return the normalized data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $format;

    /**
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param string                $uploadDirectory
     * @param string                $format
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        $uploadDirectory,
        $format
    ) {
        $this->normalizer        = $normalizer;
        $this->denormalizer      = $denormalizer;
        $this->uploadDirectory   = $uploadDirectory;
        $this->format            = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data['media'] = $this->prepareVariantGroupMedia($item);

        $parameters = $this->stepExecution->getJobParameters();
        $decimalSeparator = $parameters->get('decimalSeparator');
        $dateFormat = $parameters->get('dateFormat');
        $data['variant_group'] = $this->normalizer->normalize(
            $item,
            $this->format,
            [
                'with_variant_group_values' => true,
                'identifier'                => $item->getCode(),
                'decimal_separator'         => $decimalSeparator,
                'date_format'               => $dateFormat,
            ]
        );

        return $data;
    }

    /**
     * Prepares media files present in the product template of the variant group for export.
     * Returns an array of files to be copied from 'filePath' to 'exportPath'.
     *
     * @param GroupInterface $group
     *
     * @throws InvalidItemException If a media file is not found
     *
     * @return array
     */
    protected function prepareVariantGroupMedia(GroupInterface $group)
    {
        $mediaValues = $this->getProductTemplateMediaValues($group->getProductTemplate());

        if (count($mediaValues) < 1) {
            return [];
        }

        try {
            return $this->normalizer->normalize(
                $mediaValues,
                $this->format,
                ['field_name' => 'media', 'prepare_copy' => true, 'identifier' => $group->getCode()]
            );
        } catch (FileNotFoundException $e) {
            throw new InvalidItemException(
                $e->getMessage(),
                new ObjectInvalidItem(
                    [
                        'item'            => $group->getCode(),
                        'uploadDirectory' => $this->uploadDirectory,
                    ]
                )
            );
        }
    }

    /**
     * Normalizes and returns the media values of a product template
     *
     * @param ProductTemplateInterface|null $template
     *
     * @return \Pim\Component\Catalog\Model\ProductValueInterface[]
     */
    protected function getProductTemplateMediaValues(ProductTemplateInterface $template = null)
    {
        if (null === $template) {
            return [];
        }

        $values = $this->denormalizer->denormalize($template->getValuesData(), 'ProductValue[]', 'json');

        return $values->filter(
            function ($value) {
                return in_array(
                    $value->getAttribute()->getAttributeType(),
                    [AttributeTypes::IMAGE, AttributeTypes::FILE]
                );
            }
        )->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
