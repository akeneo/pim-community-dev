<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\ImportExportBundle\Reader\File\FileReader;

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
     * Get the code field
     *
     * @return string
     */
    public function getCodeField()
    {
        return $this->codeField;
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
            $labels = array();
            foreach ($fileData as $row) {
                $labels = array_unique(array_merge($labels, array_keys($row)));
            }
            foreach ($fileData as $key => $row) {
                $missing = array_diff($labels, array_keys($row));
                foreach ($missing as $label) {
                    $fileData[$key][$label] = null;
                }
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
