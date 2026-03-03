<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByChannelOrLocaleCommand
{
    /**
     * @param array<string> $localeCodes
     */
    public function __construct(
        public readonly string $channelCode,
        public readonly array $localeCodes,
    ) {
    }
}
