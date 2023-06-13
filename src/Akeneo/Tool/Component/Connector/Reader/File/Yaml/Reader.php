<?php

namespace Akeneo\Tool\Component\Connector\Reader\File\Yaml;

use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Exception\InvalidYamlFileException;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Reader implements FileReaderInterface, TrackableItemReaderInterface, StatefulInterface
{
    protected bool $uploadAllowed = false;
    protected ?StepExecution $stepExecution = null;
    protected ?\ArrayIterator $yaml = null;

    /**
     * @param ArrayConverterInterface $converter
     * @param bool                    $multiple
     * @param string                  $codeField
     */
    public function __construct(
        private ArrayConverterInterface $converter,
        private string $rootLevel,
        private bool $multiple = false,
        private string $codeField = 'code'
    ) {
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

    public function totalItems(): int
    {
        return count($this->getFileData() ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->initYaml()) {
            return null;
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

    private function initYaml(): bool
    {
        if (null === $this->yaml) {
            $fileData = $this->getFileData();
            if (null === $fileData) {
                return false;
            }
            $this->yaml = new \ArrayIterator($fileData);
        }

        return true;
    }

    /**
     * Returns the file data
     *
     * @return array
     * @throws InvalidYamlFileException
     */
    protected function getFileData()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->get('storage')['file_path'];

        if (!file_exists($filePath)) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $filePath));
        }

        $fileContent = file_get_contents($filePath);
        if (false === $fileContent) {
            return null;
        }

        $this->stepExecution?->setSummary(['item_position' => 0]);

        $yamlContent = Yaml::parse($fileContent);
        if (!array_key_exists($this->rootLevel, $yamlContent)) {
            throw InvalidYamlFileException::doesNotContainRootLevel($this->rootLevel);
        }

        $fileData = $yamlContent[$this->rootLevel];
        if (null === $fileData) {
            return null;
        }

        foreach ($fileData as $key => $row) {
            if (!is_array($row)) {
                throw InvalidYamlFileException::rowShouldBeAnArray($key, $row);
            }

            if ($this->codeField && !isset($row[$this->codeField])) {
                $fileData[$key][$this->codeField] = $key;
            }
        }

        return $this->multiple ? [$fileData] : $fileData;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $state = []): void
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

    public function getState(): array
    {
        return null !== $this->yaml ? ['position' => $this->yaml->key()] : [];
    }

    public function rewindToState(int $key): void
    {
        if (!$this->initYaml())
        {
            return;
        }

        $this->yaml->current();
        while ($this->yaml->key() < $key) {
            $this->yaml->next();
        }
    }
}
