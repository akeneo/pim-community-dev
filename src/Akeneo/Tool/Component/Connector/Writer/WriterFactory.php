<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer;

use Box\Spout\Writer\WriterFactory as SpoutWriterFactory;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
