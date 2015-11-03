<?php

namespace Pim\Bundle\ConnectorBundle\Writer\File;

use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;

/**
 * Used by jobs using ProductExportController, use the mainContext configuration in order to build the file path.
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContextableCsvWriter extends CsvWriter
{
    /** @var array */
    protected $context = [];

    /** @var  int */
    protected $csvFileNumber;

    /** @var string */
    protected $baseFilePath;

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the step element configuration
     *
     * @param array $config
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!isset($config['mainContext'])) {
            return;
        }

        $this->context = $config['mainContext'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if (null === $this->baseFilePath) {
            $this->resolvedFilePath = parent::getPath();

            foreach ($this->context as $key => $value) {
                $this->resolvedFilePath = strtr($this->baseFilePath, ['%' . $key . '%' => $value]);
            }
            $this->baseFilePath = $this->resolvedFilePath;
        }

        return $this->resolvedFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->csvFileNumber = 0;
        $this->resolvedFilePath = $this->getPath();
    }

    /**
     * Increments file number in file name
     */
    public function incrementFileNumber()
    {
        if ($this->csvFileNumber == 0 && false === strpos($this->baseFilePath, '%filenumber%')) {
            $this->baseFilePath = str_replace('.csv', '-%filenumber%.csv', $this->baseFilePath);
        }
        $this->resolvedFilePath = strtr($this->baseFilePath, [
            '%filenumber%' => ($this->csvFileNumber++ + 2)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (0 === count($this->items)) {
            return;
        }

        parent::flush();
        $this->items = [];
    }
}
