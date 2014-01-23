<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;

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
    protected $homogenize = false;

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
     * @param boolean $homogenize
     * @param string  $codeField
     */
    public function __construct($multiple = false, $homogenize = false, $codeField = 'code')
    {
        $this->codeField = $codeField;
        $this->multiple = $multiple;
        $this->homogenize = $homogenize;
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

        if ($this->homogenize) {
            $fileData = $this->homogenizeData($fileData);
        }

        return $this->multiple ? [$fileData] : $fileData;
    }

    /**
     * Homogenize the read data
     *
     * @param array $data
     *
     * @return array
     */
    protected function homogenizeData($data)
    {
        $labels = [];
        foreach ($data as $row) {
            $labels = array_unique(array_merge($labels, array_keys($row)));
        }
        foreach ($data as $key => $row) {
            $data[$key] += array_fill_keys(
                array_diff($labels, array_keys($row)),
                null
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
