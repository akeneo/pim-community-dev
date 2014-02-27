<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Symfony\Component\Yaml\Yaml;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReader extends FileReader implements ItemReaderInterface
{
    /**
     * @var string
     */
    protected $codeField = 'code';

    /**
     * @var boolean
     */
    protected $multiple = false;

    /**
     * @var \ArrayIterator
     */
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
        return array();
    }
}
