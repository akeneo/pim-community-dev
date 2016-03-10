<?php

namespace Pim\Component\Connector\Reader\File\Product;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Connector\Reader\File\CsvReader;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

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
    /** @var MediaPathTransformer */
    protected $mediaPathTransformer;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var array */
    protected $dateFormats;

    /**
     * @param FileIteratorInterface $fileIterator
     * @param MediaPathTransformer  $mediaPathTransformer
     * @param array                 $decimalSeparators
     * @param array                 $dateFormats
     */
    public function __construct(
        FileIteratorInterface $fileIterator,
        MediaPathTransformer $mediaPathTransformer,
        array $decimalSeparators,
        array $dateFormats
    ) {
        parent::__construct($fileIterator);

        $this->mediaPathTransformer = $mediaPathTransformer;
        $this->decimalSeparators    = $decimalSeparators;
        $this->dateFormats          = $dateFormats;
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

        return $this->mediaPathTransformer->transform($data, $this->fileIterator->getDirectoryPath());
    }
}
