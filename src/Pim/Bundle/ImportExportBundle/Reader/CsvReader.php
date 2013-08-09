<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Csv reader
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Assert\File(groups={"Execution"})
     */
    protected $filePath;
    protected $lineLength = 0;
    protected $delimiter = ';';
    protected $enclosure = '"';
    protected $escape = '\\';

    private $handle;
    private $columnsCount;

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setLineLength($lineLength)
    {
        $this->lineLength = $lineLength;
    }

    public function getLineLength()
    {
        return $this->lineLength;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function setEscape($escape)
    {
        $this->escape = $escape;
    }

    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        if (!$this->handle) {
            $this->handle = fopen($this->filePath, 'r');
        }

        $data = fgetcsv($this->handle, $this->lineLength, $this->delimiter, $this->enclosure, $this->escape);
        if (!$this->columnsCount) {
            $this->columnsCount = count($data);
        } elseif (is_array($data) && $this->columnsCount !== count($data)) {
            throw new \Exception(
                sprintf(
                    'Expecting to have %d columns, actually have %d.',
                    $this->columnsCount,
                    count($data)
                )
            );
        }

        return $data ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'filePath'   => array(),
            'lineLength' => array(),
            'delimiter'  => array(),
            'enclosure'  => array(),
            'escape'     => array(),
        );
    }
}
