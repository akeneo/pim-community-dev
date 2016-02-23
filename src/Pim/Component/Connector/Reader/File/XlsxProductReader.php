<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product XSLX reader
 */
class XlsxProductReader extends XlsxReader implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /** @var string[] Media attribute codes */
    protected $mediaAttributes;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var array */
    protected $dateFormats;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository attribute repository
     * @param array                        $decimalSeparators   decimal separators defined in config
     * @param array                        $dateFormats         format dates defined in config
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->decimalSeparators   = $decimalSeparators;
        $this->dateFormats         = $dateFormats;
    }

    /**
     * Set the media attributes
     *
     * @param array|null $mediaAttributes
     *
     * @return CsvProductReader
     */
    public function setMediaAttributes($mediaAttributes)
    {
        $this->mediaAttributes = $mediaAttributes;

        return $this;
    }

    /**
     * Get the media attributes
     *
     * @return string[]
     */
    public function getMediaAttributes()
    {
        if (null === $this->mediaAttributes) {
            $this->mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();
        }

        return $this->mediaAttributes;
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
     * Get the separator for decimal
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * Set the format for date field
     *
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Get the format for date field
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.import.filePath.label',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.uploadAllowed.label',
                    'help'  => 'pim_connector.import.uploadAllowed.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.import.delimiter.label',
                    'help'  => 'pim_connector.import.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.import.enclosure.label',
                    'help'  => 'pim_connector.import.enclosure.help'
                ]
            ],
            'escape' => [
                'options' => [
                    'label' => 'pim_connector.import.escape.label',
                    'help'  => 'pim_connector.import.escape.help'
                ]
            ],
            'mediaAttributes' => [
                'system' => true
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices' => $this->decimalSeparators,
                    'select2' => true,
                    'label'   => 'pim_connector.import.decimalSeparator.label',
                    'help'    => 'pim_connector.import.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices' => $this->dateFormats,
                    'select2' => true,
                    'label'   => 'pim_connector.import.dateFormat.label',
                    'help'    => 'pim_connector.import.dateFormat.help'
                ]
            ]
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $data)
    {
        foreach ($data as $code => $value) {
            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;
            $value = trim($value);

            if (in_array($attributeCode, $this->getMediaAttributes()) && !empty($value)) {
                $data[$code] = dirname($this->filePath) . DIRECTORY_SEPARATOR . $value;
            }
        }

        return $data;
    }

    /**
     * Remove the extracted directory
     */
    public function __destruct()
    {
        if ($this->extractedPath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->extractedPath);
        }
    }

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return [
            new Assert\NotBlank(),
            new AssertFile(
                [
                    'allowedExtensions' => ['xlsx', 'zip']
                ]
            )
        ];
    }
}
