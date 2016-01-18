<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /** @var string */
    protected $filePath;

    /** @var string */
    protected $delimiter = ';';

    /** @var string */
    protected $enclosure = '"';

    /** @var string */
    protected $escape = '\\';

    /** @var bool */
    protected $uploadAllowed = false;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $extractedPath;

    /** @var \SplFileObject */
    protected $csv;

    /** @var array */
    protected $fieldNames = [];

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
                    'allowedExtensions' => ['csv', 'zip']
                ]
            )
        ];
    }

    /**
     * Set uploaded file
     *
     * @param File $uploadedFile
     *
     * @return CsvReader
     */
    public function setUploadedFile(File $uploadedFile)
    {
        $this->filePath = $uploadedFile->getRealPath();
        $this->csv = null;

        return $this;
    }

    /**
     * Set file path
     *
     * @param string $filePath
     *
     * @return CsvReader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        $this->csv = null;

        return $this;
    }

    /**
     * Get file path
     *
     * @return string $filePath
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set delimiter
     *
     * @param string $delimiter
     *
     * @return CsvReader
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get delimiter
     *
     * @return string $delimiter
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set enclosure
     *
     * @param string $enclosure
     *
     * @return CsvReader
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Get enclosure
     *
     * @return string $enclosure
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set escape
     *
     * @param string $escape
     *
     * @return CsvReader
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Get escape
     *
     * @return string $escape
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set the uploadAllowed property
     *
     * @param bool $uploadAllowed
     *
     * @return CsvReader
     */
    public function setUploadAllowed($uploadAllowed)
    {
        $this->uploadAllowed = $uploadAllowed;

        return $this;
    }

    /**
     * Get the uploadAllowed property
     *
     * @return bool $uploadAllowed
     */
    public function isUploadAllowed()
    {
        return $this->uploadAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->csv) {
            $this->initializeRead();
        }

        $data = $this->csv->fgetcsv();

        if (false !== $data) {
            if ([null] === $data || null === $data) {
                return null;
            }
            if ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('read_lines');
            }

            if (count($this->fieldNames) !== count($data)) {
                throw new InvalidItemException(
                    'pim_connector.steps.csv_reader.invalid_item_columns_count',
                    $data,
                    [
                        '%totalColumnsCount%' => count($this->fieldNames),
                        '%itemColumnsCount%'  => count($data),
                        '%csvPath%'           => $this->csv->getRealPath(),
                        '%lineno%'            => $this->csv->key()
                    ]
                );
            }

            $data = array_combine($this->fieldNames, $data);
        } elseif ($this->csv->eof()) {
            $data = null;
        } else {
            throw new \RuntimeException('An error occurred while reading the csv.');
        }

        return $data;
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Extract the zip archive to be imported
     *
     * @throws \RuntimeException When archive cannot be opened or extracted
     *                           or does not contain exactly one csv file
     */
    protected function extractZipArchive()
    {
        $archive = new \ZipArchive();

        $status = $archive->open($this->filePath);

        if (true !== $status) {
            throw new \RuntimeException(sprintf('Error "%d" occurred while opening the zip archive.', $status));
        } else {
            $targetDir = sprintf(
                '%s/%s_%d_%s',
                pathinfo($this->filePath, PATHINFO_DIRNAME),
                pathinfo($this->filePath, PATHINFO_FILENAME),
                $this->stepExecution->getId(),
                md5(microtime() . $this->stepExecution->getId())
            );

            if ($archive->extractTo($targetDir) !== true) {
                throw new \RuntimeException('Error occurred while extracting the zip archive.');
            }

            $archive->close();
            $this->extractedPath = $targetDir;

            $csvFiles = glob($targetDir . '/*.[cC][sS][vV]');

            $csvCount = count($csvFiles);
            if (1 !== $csvCount) {
                throw new \RuntimeException(
                    sprintf(
                        'Expecting the root directory of the archive to contain exactly 1 csv file, found %d',
                        $csvCount
                    )
                );
            }

            $this->filePath = current($csvFiles);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null !== $this->csv) {
            $this->csv->rewind();
        }
    }

    /**
     * Initialize read process by extracting zip if needed, setting CSV options
     * and settings field names.
     */
    protected function initializeRead()
    {
        // TODO mime_content_type is deprecated, use Symfony\Component\HttpFoundation\File\MimeTypeMimeTypeGuesser?
        if ('application/zip' === mime_content_type($this->filePath)) {
            $this->extractZipArchive();
        }

        $this->csv = new \SplFileObject($this->filePath);
        $this->csv->setFlags(
            \SplFileObject::READ_CSV   |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY
        );
        $this->csv->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
        $this->fieldNames = $this->csv->fgetcsv();
    }
}
