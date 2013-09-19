<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

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
    protected $directoryName;
    
    /**
     * @Assert\NotBlank
     */
    protected $fileName;

    private $handler;

    /**
     * Set the filename 
     *
     * @param string 
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the directory name 
     *
     * @param string $path
     */
    public function setDirectoryName($directoryName)
    {
        $this->directoryName = $directoryName;
        return $this;
    }

    /**
     * Get the directory name
     *
     * @return string
     */
    public function getDirectoryName()
    {
        return $this->directoryName;
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf(
            '%s/%s',
            $this->directoryName,
            strtr(
                $this->fileName,
                array(
                    '%datetime%' => date('Y-m-d_H:i:s')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        if (!$this->handler) {
            $this->handler = fopen($this->getPath(), 'w');
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
            'directoryName' => array(
                'options' => array()
            ),
            'fileName'=>array(
                'options'=>array(
                    'data'=>'export_%datetime%.csv'
                )
            )
        );
    }
}
