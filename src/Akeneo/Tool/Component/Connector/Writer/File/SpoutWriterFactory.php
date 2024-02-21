<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer\File;

use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\WriterInterface;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SpoutWriterFactory
{
    public const XLSX = 'xlsx';
    public const CSV = 'csv';

    public static function create(string $type, array $normalizedOptions = []): WriterInterface
    {
        switch ($type) {
            case self::XLSX:
                $options = new XlsxOptions();
                break;
            case self::CSV:
                $options = new CsvOptions();
                $options->FIELD_DELIMITER = $normalizedOptions['fieldDelimiter'] ?? $options->FIELD_DELIMITER;
                $options->FIELD_ENCLOSURE = $normalizedOptions['filedEnclosure'] ?? $options->FIELD_ENCLOSURE;
                $options->SHOULD_ADD_BOM = $normalizedOptions['shouldAddBOM'] ?? $options->SHOULD_ADD_BOM;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid writer type', $type));
        }

        return match ($type) {
            self::XLSX => new XlsxWriter($options),
            self::CSV => new CsvWriter($options),
        };
    }
}
