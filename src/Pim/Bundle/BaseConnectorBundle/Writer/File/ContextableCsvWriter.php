<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

/**
 * Used by jobs using ProductExportController, use the mainContext configuration in order to build the file path.
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6, this behavior is now handled by Pim\Component\Connector\Writer\File\FilePathResolver
 */
class ContextableCsvWriter extends CsvWriter
{
    /** @var array */
    protected $context = [];

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
        if (null === $this->resolvedFilePath) {
            $this->resolvedFilePath = parent::getPath();

            foreach ($this->context as $key => $value) {
                $this->resolvedFilePath = strtr($this->resolvedFilePath, ['%' . $key . '%' => $value]);
            }
        }

        return $this->resolvedFilePath;
    }
}
