<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryChannels implements ChannelsInterface
{
    private array $channelsIdsByCodes;
    private array $channelsCodesByIds;

    /**
     * @param array<string, int> $channelsIdsByCodes
     */
    public function __construct(array $channelsIdsByCodes)
    {
        $this->channelsIdsByCodes = $channelsIdsByCodes;
        $this->channelsCodesByIds = array_flip($channelsIdsByCodes);
    }

    public function getIdByCode(string $code): ?int
    {
        return $this->channelsIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        return $this->channelsCodesByIds[$id] ?? null;
    }
}
