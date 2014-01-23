<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Pim\Bundle\ImportExportBundle\Archiver\InvalidItemsCsvArchiver;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvReader extends FileReader implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @AssertFile(
     *     groups={"Execution"},
     *     allowedExtensions={"csv", "zip"},
     *     mimeTypes={
     *         "text/csv",
     *         "text/comma-separated-values",
     *         "text/plain",
     *         "application/csv",
     *         "application/zip"
     *     }
     * )
     */
    protected $filePath;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     */
    protected $enclosure = '"';

    /**
     * @Assert\NotBlank
     */
    protected $escape = '\\';

    /**
     * @var boolean
     *
     * @Assert\Type(type="bool")
     * @Assert\True(groups={"UploadExecution"})
     */
    protected $uploadAllowed = false;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var string $file
     */
    protected $file;

    /**
     * @var SplFileObject
     */
    protected $csv;

    /** @var InvalidItemsCsvArchiver */
    protected $archiver;

    /**
     * @param InvalidItemsCsvArchiver $archiver
     */
    public function __construct(InvalidItemsCsvArchiver $archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * Remove the extracted directory
     */
    public function __destruct()
    {
        if ($this->file !== $this->filePath) {
            $fileSystem = new Filesystem();
            $fileSystem->remove(dirname($this->file));
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
                    'allowedExtensions' => ['csv', 'zip'],
                    'mimeTypes'         => [
                        'text/csv',
                        'text/comma-separated-values',
                        'text/plain',
                        'application/csv',
                        'application/zip'
                    ]
                ]
            )
        ];
    }

    /**
     * Set uploaded file
     * @param string $uploadedFile
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
     * @return string $filePath
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set delimiter
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
     * @return string $delimiter
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set enclosure
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
     * @return string $enclosure
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set escape
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
     * @return string $escape
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set the uploadAllowed property
     * @param boolean $uploadAllowed
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
     * @return boolean $uploadAllowed
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
            if (mime_content_type($this->filePath) === 'application/zip') {
                $this->extractZipArchive();
            } else {
                $this->file = $this->filePath;
            }

            $this->csv = new \SplFileObject($this->file);
            $this->csv->setFlags(
                \SplFileObject::READ_CSV   |
                \SplFileObject::READ_AHEAD |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
            );
            $this->csv->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
            $this->fieldNames = $this->csv->fgetcsv();
            $this->archiver->setHeader($this->fieldNames);
        }

        $data = $this->csv->fgetcsv();

        if (false !== $data) {
            if ($data === [null] || $data === null) {
                return null;
            }
            if ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('read');
            }

            if (count($this->fieldNames) !== count($data)) {
                throw new InvalidItemException(
                    'pim_import_export.steps.csv_reader.invalid_item_columns_count',
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
        } else {
            throw new \RuntimeException('An error occured while reading the csv.');
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
                    'label' => 'pim_import_export.import.filePath.label',
                    'help'  => 'pim_import_export.import.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_import_export.import.uploadAllowed.label',
                    'help'  => 'pim_import_export.import.uploadAllowed.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_import_export.import.delimiter.label',
                    'help'  => 'pim_import_export.import.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_import_export.import.enclosure.label',
                    'help'  => 'pim_import_export.import.enclosure.help'
                ]
            ],
            'escape' => [
                'options' => [
                    'label' => 'pim_import_export.import.escape.label',
                    'help'  => 'pim_import_export.import.escape.help'
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
     * @throws \RuntimeException When archive cannot be opened or extracted
     *                           or does not contain exactly one csv file
     */
    protected function extractZipArchive()
    {
        $archive = new \ZipArchive();

        $status = $archive->open($this->filePath);

        if ($status !== true) {
            throw new \RuntimeException(sprintf('Error "%d" occured while opening the zip archive.', $status));
        } else {
            $targetDir = sprintf(
                '%s/%s_%d_%s',
                pathinfo($this->filePath, PATHINFO_DIRNAME),
                pathinfo($this->filePath, PATHINFO_FILENAME),
                $this->stepExecution->getId(),
                md5(microtime() . $this->stepExecution->getId())
            );

            if ($archive->extractTo($targetDir) !== true) {
                throw new \RuntimeException('Error occured while extracting the zip archive.');
            }

            $archive->close();

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

            $this->file = reset($csvFiles);
        }
    }
}
