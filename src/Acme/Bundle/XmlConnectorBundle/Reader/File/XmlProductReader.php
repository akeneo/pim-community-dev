<?php

namespace Acme\Bundle\XmlConnectorBundle\Reader\File;

use Akeneo\Component\Batch\Item\FileInvalidItem;
use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Exception\InvalidItemFromViolationsException;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlProductReader implements
    ItemReaderInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var array */
    protected $xml;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ArrayConverterInterface */
    protected $converter;

    /**
     * @param ArrayConverterInterface $converter
     */
    public function __construct(ArrayConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function read()
    {
        if (null === $this->xml) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filePath = $jobParameters->get('filePath');
            // for example purpose, we should use XML Parser to read line per line
            $this->xml = simplexml_load_file($filePath, 'SimpleXMLIterator');
            $this->xml->rewind();
            $this->stepExecution->addSummaryInfo('read_lines', -1);
        }

        if ($data = $this->xml->current()) {
            $item = [];
            foreach ($data->attributes() as $attributeName => $attributeValue) {
                $item[$attributeName] = (string) $attributeValue;
            }
            $this->xml->next();

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('read_lines');
            }

            try {
                $item = $this->converter->convert($item);
            } catch (DataArrayConversionException $e) {
                $this->skipItemFromConversionException($this->xml->current(), $e);
            }

            return $item;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->xml = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
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
                new FileInvalidItem($item, ($this->stepExecution->getSummaryInfo('read_lines') + 1)),
                [],
                0,
                $exception
            );
        }

        $invalidItem = new FileInvalidItem(
            $item,
            ($this->stepExecution->getSummaryInfo('read_lines') + 1)
        );

        throw new InvalidItemException($exception->getMessage(), $invalidItem, [], 0, $exception);
    }
}
