<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\WriterAbstract;

final class WriterFactory
{
    public static function create($writerType): WriterAbstract
    {
        switch ($writerType) {
            case Type::XLSX:
                $writer = WriterEntityFactory::createXLSXWriter();
                break;
            case Type::ODS:
                $writer = WriterEntityFactory::createODSWriter();
                break;
            case Type::CSV:
                $writer = WriterEntityFactory::createCSVWriter();
                break;
            default:
                throw new UnsupportedTypeException();
        }


        return $writer;
    }
}
