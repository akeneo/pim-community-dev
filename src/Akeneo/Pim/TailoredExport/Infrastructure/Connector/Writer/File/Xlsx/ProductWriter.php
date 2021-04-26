<?php

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx;

use Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\AbstractItemMediaWriter;

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
