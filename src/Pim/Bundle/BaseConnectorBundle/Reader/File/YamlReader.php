<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReader extends FileReader implements ItemReaderInterface, UploadedFileAwareInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @AssertFile(
     *     groups={"Execution"},
     *     allowedExtensions={"yml", "yaml"}
     * )
     */
    protected $filePath;

    /** @var string */
    protected $codeField = 'code';

    /** @var bool */
    protected $multiple = false;

    /**
     * @var bool
     *
     * @Assert\Type(type="bool")
     * @Assert\True(groups={"UploadExecution"})
     */
    protected $uploadAllowed = false;

    /** @var \ArrayIterator */
    protected $yaml;

    /**
     * Constructor
     *
     * @param boolean $multiple
     * @param string  $codeField
     */
    public function __construct($multiple = false, $codeField = 'code')
    {
        $this->codeField = $codeField;
        $this->multiple = $multiple;
    }

    /**
     * Set file path
     * @param string $filePath
     *
     * @return YamlReader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        $this->yaml     = null;

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
     * Set the multiple attribute
     *
     * @param boolean $multiple
     *
     * @return YamlReader
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set the code field
     *
     * @param string $codeField
     *
     * @return YamlReader
     */
    public function setCodeField($codeField)
    {
        $this->codeField = $codeField;

        return $this;
    }

    /**
     * Get the code field
     *
     * @return string
     */
    public function getCodeField()
    {
        return $this->codeField;
    }


    /**
     * Set the uploadAllowed property
     *
     * @param bool $uploadAllowed
     *
     * @return YamlReader
     */
    public function setUploadAllowed($uploadAllowed)
    {
        $this->uploadAllowed = $uploadAllowed;

        return $this;
    }

    /**
     * Get the uploadAllowed property
     *
     * @return bool
     */
    public function isUploadAllowed()
    {
        return $this->uploadAllowed;
    }

    /**
     * Get uploaded file constraints
     *
     * @return array
     */
    public function getUploadedFileConstraints()
    {
        return array(
            new Assert\NotBlank(),
            new AssertFile(
                array(
                    'allowedExtensions' => array('yml', 'yaml')
                )
            )
        );
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
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->yaml) {
            $this->yaml = new \ArrayIterator($this->getFileData());
        }

        if ($data = $this->yaml->current()) {
            $this->yaml->next();

            return $data;
        }

        return null;
    }

    /**
     * Returns the file data
     *
     * @return array
     */
    protected function getFileData()
    {
        $fileData = current(Yaml::parse($this->filePath));

        foreach ($fileData as $key => $row) {
            if ($this->codeField && !isset($row[$this->codeField])) {
                $fileData[$key][$this->codeField] = $key;
            }
        }

        return $this->multiple ? array($fileData) : $fileData;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => array(
                'options' => array(
                    'label' => 'pim_base_connector.import.yamlFilePath.label',
                    'help'  => 'pim_base_connector.import.yamlFilePath.help'
                )
            ),
            'uploadAllowed' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.import.uploadAllowed.label',
                    'help'  => 'pim_base_connector.import.uploadAllowed.help'
                )
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null !== $this->yaml) {
            $this->yaml->rewind();
        }
    }
}
