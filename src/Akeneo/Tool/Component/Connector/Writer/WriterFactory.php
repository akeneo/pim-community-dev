<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer;

use Box\Spout\Writer\WriterFactory as SpoutWriterFactory;

/**
 * @todo CPM-248
 */
final class WriterFactory extends SpoutWriterFactory
{
    public static function create($writerType)
    {
        $writer = parent::create($writerType);
        $writer->setGlobalFunctionsHelper(new GlobalFunctionsHelper());

        return $writer;
    }
}
