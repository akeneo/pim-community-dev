<?php

namespace Pim\Component\Connector\Reader\File;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Localization\Localizer\AbstractNumberLocalizer;
use Pim\Component\Localization\Localizer\DateLocalizer;

/**
 * Product csv reader
 *
 * This specialized csv reader exists to replace relative media path to absolute path, in order for later process to
 * know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductReader extends CsvReader
{
    /** @var string[] Media attribute codes */
    protected $mediaAttributes;

    /** @var string */
    protected $decimalSeparator = AbstractNumberLocalizer::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = DateLocalizer::DEFAULT_DATE_FORMAT;

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
        return array_merge(
            parent::getConfigurationFields(),
            [
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
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data)) {
            return $data;
        }

        return $this->transformMediaPathToAbsolute($data);
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
}
