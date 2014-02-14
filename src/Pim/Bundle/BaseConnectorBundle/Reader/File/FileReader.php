<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;

/**
 * File reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var string */
    protected $filePath;

    /**
     * Get the file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set the file path
     *
     * @param string $filePath
     *
     * @return FileReader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        throw new \Exception('Not implemented yet.');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
