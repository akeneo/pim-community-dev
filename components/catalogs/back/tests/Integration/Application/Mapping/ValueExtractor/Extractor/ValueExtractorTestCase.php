<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStringsValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\BooleanValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ValueExtractorInterface;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ValueExtractorTestCase extends IntegrationTestCase
{
    protected const TARGET_TYPES_INTERFACES_MAPPING = [
        ValueExtractorInterface::TARGET_TYPE_ARRAY_OF_STRINGS => ArrayOfStringsValueExtractorInterface::class,
        ValueExtractorInterface::TARGET_TYPE_BOOLEAN => BooleanValueExtractorInterface::class,
        ValueExtractorInterface::TARGET_TYPE_NUMBER => NumberValueExtractorInterface::class,
        ValueExtractorInterface::TARGET_TYPE_STRING => StringValueExtractorInterface::class,
    ];
}
