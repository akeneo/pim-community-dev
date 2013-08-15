<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\ProductBundle\Validator\Constraints as PimAssert;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;

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
     * @PimAssert\File(groups={"Execution"}, allowedExtensions={"csv"})
     */
    protected $filePath;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     */
    protected $enclosure = '"';

    /**
     * @Assert\NotBlank
     */
    protected $escape = '\\';

    private $csv;

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
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
        if (null === $this->csv) {
            $this->csv = new \SplFileObject($this->filePath);
            $this->csv->setFlags(\SplFileObject::READ_CSV);
            $this->csv->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
            $this->fieldNames = $this->csv->fgetcsv();
        }

        if ($data = $this->csv->fgetcsv()) {
            if (array(null) === $data) {
                return;
            }

            if (count($this->fieldNames) !== count($data)) {
                throw new \Exception(
                    sprintf(
                        'Expecting to have %d columns, actually have %d.',
                        count($this->fieldNames),
                        count($data)
                    )
                );
            }

            $data = array_combine($this->fieldNames, $data);
        } else {
            throw new \RuntimeException('An error occured while reading the csv.');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'filePath'   => array(),
            'delimiter'  => array(),
            'enclosure'  => array(),
            'escape'     => array(),
        );
    }
}
