<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Abstract file writer to handle file naming and configuration-related logic.
 * write() method must be implemented by children.
 *
 * All the writers should output to files that are named with the job code.
 * Like "csv_family_export" or "xlsx_product_export" for instance.
 *
 * Some writers may output to several files in case of need, like "xlsx_product_export_1",
 * "xlsx_product_export"_2" etc..
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFileWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var FilePathResolverInterface */
    protected $filePathResolver;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var array */
    protected $filePathResolverOptions;

    /** @var Filesystem */
    protected $localFs;

    /**
     * @param FilePathResolverInterface $filePathResolver
     */
    public function __construct(FilePathResolverInterface $filePathResolver)
    {
        $this->filePathResolver = $filePathResolver;
        $this->filePathResolverOptions = [
            'parameters' => ['%datetime%' => date('Y-m-d_H:i:s')]
        ];
        $this->localFs = new Filesystem();
    }

    /**
     * TODO: should be dropped at the end
     *
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get('filePath');

        if ($parameters->has('mainContext')) {
            $mainContext = $parameters->get('mainContext');
            foreach ($mainContext as $key => $value) {
                $this->filePathResolverOptions['parameters']['%' . $key . '%'] = $value;
            }
        }

        return $this->filePathResolver->resolve($filePath, $this->filePathResolverOptions);
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
