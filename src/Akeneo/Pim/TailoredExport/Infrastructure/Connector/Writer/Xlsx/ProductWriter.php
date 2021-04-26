<?php

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\Xlsx;

use Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\AbstractItemMediaWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

class ProductWriter extends AbstractItemMediaWriter {
    /**
     * @return array<string, mixed>
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        return ['type' => 'xlsx'];
    }
}
