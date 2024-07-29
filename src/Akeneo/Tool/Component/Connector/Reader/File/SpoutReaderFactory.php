<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Reader\File;

use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Options as XlsxOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SpoutReaderFactory
{
    public const XLSX = 'xlsx';
    public const CSV = 'csv';

    /**
     * @return XlsxReader|CsvReader
     */
    public static function create(string $type, array $normalizedOptions = []): ReaderInterface
    {
        switch ($type) {
            case self::XLSX:
                $options = new XlsxOptions();
                $options->SHOULD_FORMAT_DATES = $normalizedOptions['shouldFormatDates'] ?? $options->SHOULD_FORMAT_DATES;
                $options->SHOULD_PRESERVE_EMPTY_ROWS = $normalizedOptions['shouldPreserveEmptyRows'] ?? $options->SHOULD_PRESERVE_EMPTY_ROWS;
                break;
            case self::CSV:
                $options = new CsvOptions();
                $options->FIELD_DELIMITER = $normalizedOptions['fieldDelimiter'] ?? $options->FIELD_DELIMITER;
                $options->FIELD_ENCLOSURE = $normalizedOptions['fieldEnclosure'] ?? $options->FIELD_ENCLOSURE;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid reader type', $type));
        }

        return match ($type) {
            self::XLSX => new XlsxReader($options),
            self::CSV => new CsvReader($options),
        };
    }
}
