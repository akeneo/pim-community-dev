<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

/**
 * Write data into a file on the filesystem
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * @Assert\NotBlank
     */
    protected $path;

    private $handler;

    /**
     * Set the file path in which to write the data
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        if (!$this->handler) {
            $this->handler = fopen($this->path, 'w');
        }

        foreach ($data as $entry) {
            fwrite($this->handler, $entry);
        }
    }

    /**
     * Close handler when desctructing the current instance
     */
    public function __destruct()
    {
        if ($this->handler) {
            fclose($this->handler);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'path' => array(
                'options' => array()
            )
        );
    }
}
