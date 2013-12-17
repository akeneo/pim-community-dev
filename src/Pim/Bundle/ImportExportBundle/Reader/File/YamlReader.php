<?php

namespace Pim\Bundle\ImportExportBundle\Reader\File;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \ArrayIterator
     */
    protected $yaml;

    /**
     * Set file path
     * @param string $filePath
     *
     * @return YamlReader
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
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->yaml) {
            $fileData = Yaml::parse($this->filePath);
            $this->yaml = new \ArrayIterator(array_pop($fileData));
        }

        if ($data = $this->yaml->current()) {
            if (!isset($data['code'])) {
                $data['code'] = $this->yaml->key();
            }
            $this->yaml->next();

            return $data;
        }

        return null;
    }

    public function getConfigurationFields()
    {
        return array();
    }
}
