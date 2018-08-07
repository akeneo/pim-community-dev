<?php

namespace Akeneo\Tool\Component\Connector\Reader\File\Yaml;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader implements FileReaderInterface
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var bool */
    protected $multiple = false;

    /** @var string */
    protected $codeField = 'code';

    /** @var bool */
    protected $uploadAllowed = false;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \ArrayIterator */
    protected $yaml;

    /**
     * @param ArrayConverterInterface $converter
     * @param bool                    $multiple
     * @param string                  $codeField
     */
    public function __construct(ArrayConverterInterface $converter, $multiple = false, $codeField = 'code')
    {
        $this->converter = $converter;
        $this->codeField = $codeField;
        $this->multiple = $multiple;
    }

    /**
     * Set the code field
     *
     * @param string $codeField
     *
     * @return Reader
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
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->yaml) {
            $fileData = $this->getFileData();
            if (null === $fileData) {
                return null;
            }
            $this->yaml = new \ArrayIterator($fileData);
        }

        if ($data = $this->yaml->current()) {
            $this->yaml->next();
            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('item_position');
            }

            try {
                $data = $this->converter->convert($data);
            } catch (DataArrayConversionException $e) {
                $this->skipItemFromConversionException($data, $e);
            }

            return $data;
        }

        // if not used in the context of an ItemStep, the previous read file will be returned
        $this->flush();

        return null;
    }

    /**
     * Returns the file data
     *
     * @return array
     */
    protected function getFileData()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('filePath');
        $fileData = current(Yaml::parse(file_get_contents($filePath)));
        if (null === $fileData) {
            return null;
        }

        foreach ($fileData as $key => $row) {
            if ($this->codeField && !isset($row[$this->codeField])) {
                $fileData[$key][$this->codeField] = $key;
            }
        }

        return $this->multiple ? [$fileData] : $fileData;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null !== $this->yaml) {
            $this->yaml->rewind();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->yaml = null;
    }

    /**
     * @param array                        $item
     * @param DataArrayConversionException $exception
     *
     * @throws InvalidItemException
     * @throws InvalidItemFromViolationsException
     */
    protected function skipItemFromConversionException(array $item, DataArrayConversionException $exception)
    {
        if (null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }

        if (null !== $exception->getViolations()) {
            throw new InvalidItemFromViolationsException(
                $exception->getViolations(),
                new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
                [],
                0,
                $exception
            );
        }

        throw new InvalidItemException(
            $exception->getMessage(),
            new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('item_position'))),
            [],
            0,
            $exception
        );
    }
}
