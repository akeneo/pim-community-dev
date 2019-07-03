<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GenerateFlatHeadersFromAttributeCodesInterface
{
    /**
     * Generate headers from the provided attribute codes
     *
     * @return FlatFileHeader[]
     */
    public function __invoke(
        array $attributeCodes,
        string $channelCode,
        array $localeCodes
    ): array;
}
