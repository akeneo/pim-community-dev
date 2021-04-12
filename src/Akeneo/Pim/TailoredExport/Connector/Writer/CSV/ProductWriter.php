<?php

namespace Akeneo\Pim\TailoredExport\Connector\Writer\CSV;

use Akeneo\Pim\TailoredExport\Connector\Writer\AbstractItemMediaWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

class ProductWriter extends AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    public function __construct(
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FileExporterPathGeneratorInterface $fileExporterPath,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
        parent::__construct(
            $bufferFactory,
            $flusher,
            $fileExporterPath,
            $jobParamFilePath
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        $parameters = $this->stepExecution->getJobParameters();

        return [
            'type'           => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM'   => false,
        ];
    }
}
