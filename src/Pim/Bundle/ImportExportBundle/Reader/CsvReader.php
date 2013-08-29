<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\ProductBundle\Validator\Constraints\File;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\BatchBundle\Item\UploadedFileAwareInterface;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\Callback(groups={"Execution"}, methods={"isFilePathValid"})
 */
class CsvReader extends AbstractConfigurableStepElement implements ItemReaderInterface, UploadedFileAwareInterface
{
    /**
     * @File(groups={"Execution"}, allowedExtensions={"csv"})
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
     */
    protected $allowUpload = false;

    /**
     * @var SplFileObject
     */
    private $csv;

    /**
     * Apply NotBlank constraint to filePath if file upload is not allowed
     *
     * @param ExecutionContextInterface $context
     */
    public function isFilePathValid(ExecutionContextInterface $context)
    {
        if ($this->allowUpload === false && empty($this->filePath)) {
            $context->addViolationAt('filePath', 'This value should not be blank.');
        }
    }

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return array(
            new File(array('allowedExtensions' => array("csv"))),
        );
    }

    /**
     * Set uploaded file
     * @param string $uploadedFile
     *
     * @return CsvReader
     */
    public function setUploadedFile(UploadedFile $uploadedFile)
    {
        $this->filePath = $uploadedFile->getRealPath();

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
     * Set the allowUpload property
     * @param boolean $allowUpload
     *
     * @return CsvReader
     */
    public function setAllowUpload($allowUpload)
    {
        $this->allowUpload = $allowUpload;

        return $this;
    }

    /**
     * Get the allowUpload property
     * @return boolean $allowUpload
     */
    public function getAllowUpload()
    {
        return $this->allowUpload;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->csv) {
            $this->csv = new \SplFileObject($this->filePath);
            $this->csv->setFlags(
                \SplFileObject::READ_CSV   |
                \SplFileObject::READ_AHEAD |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::DROP_NEW_LINE
            );
            $this->csv->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
            $this->fieldNames = $this->csv->fgetcsv();
        }

        $data = $this->csv->fgetcsv();
        if (false !== $data) {
            if ($data === array(null) || $data === null) {
                return null;
            }

            if (count($this->fieldNames) !== count($data)) {
                throw new \Exception(
                    sprintf(
                        'Expecting to have %d columns, actually have %d.',
                        count($this->fieldNames),
                        count($data)
                    )
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
        return array(
            'filePath'    => array(),
            'allowUpload' => array(
                'type' => 'checkbox',
            ),
            'delimiter'   => array(),
            'enclosure'   => array(),
            'escape'      => array(),
        );
    }
}
