<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
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
class VariantGroupProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var string */
    protected $uploadDirectory;

    /** @var string */
    protected $format;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var array */
    protected $dateFormats;

    /**
     * @param NormalizerInterface   $normalizer
     * @param DenormalizerInterface $denormalizer
     * @param array                 $decimalSeparators
     * @param array                 $dateFormats
     * @param string                $uploadDirectory
     * @param string                $format
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        array $decimalSeparators,
        array $dateFormats,
        $uploadDirectory,
        $format
    ) {
        $this->normalizer        = $normalizer;
        $this->denormalizer      = $denormalizer;
        $this->decimalSeparators = $decimalSeparators;
        $this->dateFormats       = $dateFormats;
        $this->uploadDirectory   = $uploadDirectory;
        $this->format            = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data['media'] = $this->prepareVariantGroupMedia($item);

        $data['variant_group'] = $this->normalizer->normalize(
            $item,
            $this->format,
            [
                'with_variant_group_values' => true,
                'identifier'                => $item->getCode(),
                'decimal_separator'         => $this->decimalSeparator,
                'date_format'               => $this->dateFormat,
            ]
        );

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->decimalSeparators,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.decimalSeparator.label',
                    'help'     => 'pim_base_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->dateFormats,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.dateFormat.label',
                    'help'     => 'pim_base_connector.export.dateFormat.help'
                ]
            ],
        ];
    }

    /**
     * Set the separator for decimal
     *
     * @param string $decimalSeparator
     */
    public function setDecimalSeparator($decimalSeparator)
    {
        $this->decimalSeparator = $decimalSeparator;
    }

    /**
     * Get the delimiter for decimal
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * Set the date format for date fields
     *
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Get the date format for date fields
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
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
                [
                    'item'            => $group->getCode(),
                    'uploadDirectory' => $this->uploadDirectory,
                ]
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
}
