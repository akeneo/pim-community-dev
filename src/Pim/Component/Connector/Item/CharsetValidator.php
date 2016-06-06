<?php

namespace Pim\Component\Connector\Item;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\Exception\CharsetException;

/**
 * Check the encoding of a file.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CharsetValidator extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var string */
    protected $charset;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var int */
    protected $maxErrors;

    /** @var array */
    protected $whiteListExtension;

    /**
     * @param array  $whiteListExtension
     * @param string $charset
     * @param int    $maxErrors
     */
    public function __construct(
        array $whiteListExtension = ['xls', 'xlsx', 'zip'],
        $charset = 'UTF-8',
        $maxErrors = 10
    ) {
        $this->charset = $charset;
        $this->maxErrors = $maxErrors;
        $this->whiteListExtension = $whiteListExtension;
    }

    /**
     * Validate that the file is correctly encoded in the provided charset.
     *
     * @throws CharsetException
     * @throws \Exception
     */
    public function validate()
    {
        $filePath = $this->getPathname();
        if (!is_readable($filePath)) {
            throw new \Exception(sprintf('Unable to read the file "%s".', $filePath));
        }

        $file = new \SplFileInfo($filePath);
        if (!in_array($file->getExtension(), $this->whiteListExtension)) {
            $this->validateEncoding();
        } else {
            $this->stepExecution->addSummaryInfo(
                'charset_validator.title',
                'job_execution.summary.charset_validator.skipped'
            );
        }
    }

    /**
     * Validate that the file is correctly encoded in the provided charset.
     *
     * @throws CharsetException
     * @throws \Exception
     */
    protected function validateEncoding()
    {
        $filePath = $this->getPathname();
        if (!is_readable($filePath)) {
            throw new \Exception(sprintf('Unable to read the file "%s".', $filePath));
        }

        $handle = fopen($filePath, 'r');
        if (false === $handle) {
            throw new \Exception(sprintf('Unable to open the file "%s".', $filePath));
        }

        $errors = [];
        $lineNo = 0;
        while ((false !== $line = fgets($handle)) &&
            (count($errors) < $this->maxErrors)
        ) {
            $lineNo++;
            if (false === iconv($this->charset, $this->charset, $line)) {
                $errors[] = $lineNo;
            }
        }

        fclose($handle);

        if (count($errors) > 0) {
            $message = count($errors) === $this->maxErrors ?
                sprintf('The first %s erroneous lines are %s.', $this->maxErrors, implode(', ', $errors)) :
                sprintf('The lines %s are erroneous.', implode(', ', $errors));

            throw new CharsetException(
                sprintf('The file "%s" is not correctly encoded in %s. ', $filePath, $this->charset) .
                $message
            );
        }

        $this->stepExecution->addSummaryInfo('charset_validator.title', $this->charset . ' OK');
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * TODO: should not be duplicated everywhere
     *
     * @return string
     */
    private function getPathname()
    {
        $context = $this->stepExecution->getJobExecution()->getExecutionContext();
        if (!$context->has('workingDirectory')) {
            throw new \LogicException('The working directory is expected in the execution context.');
        }

        return $context->get('workingDirectory')->getPathname() . DIRECTORY_SEPARATOR .
            basename($this->stepExecution->getJobParameters()->get('filePath'));
    }
}
