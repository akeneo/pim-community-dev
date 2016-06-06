<?php

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Xlsx Reader
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxReader extends AbstractConfigurableStepElement implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var FileIteratorFactory */
    protected $fileIteratorFactory;

    /** @var FileIteratorInterface */
    protected $fileIterator;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param FileIteratorFactory $fileIteratorFactory
     */
    public function __construct(FileIteratorFactory $fileIteratorFactory)
    {
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->fileIterator) {
            $this->fileIterator = $this->fileIteratorFactory->create($this->getPathname());
            $this->fileIterator->rewind();
        }

        $this->fileIterator->next();

        if ($this->fileIterator->valid() && null !== $this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('read_lines');
        }

        return $this->fileIterator->current();
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
    public function flush()
    {
        $this->fileIterator = null;
    }

    /**
     * TODO: should not be duplicated everywhere
     *
     * @return string
     */
    protected function getPathname()
    {
        $context = $this->stepExecution->getJobExecution()->getExecutionContext();
        if (!$context->has('workingDirectory')) {
            throw new \LogicException('The working directory is expected in the execution context.');
        }

        return $context->get('workingDirectory')->getPathname() . DIRECTORY_SEPARATOR .
        basename($this->stepExecution->getJobParameters()->get('filePath'));
    }
}
