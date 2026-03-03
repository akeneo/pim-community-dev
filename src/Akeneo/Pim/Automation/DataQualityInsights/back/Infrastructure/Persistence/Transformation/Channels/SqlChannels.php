<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlChannels implements ChannelsInterface
{
    private array $channelIdsByCodes;

    private array $channelCodesByIds;

    private bool $channelsLoaded;

    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->channelCodesByIds = [];
        $this->channelIdsByCodes = [];
        $this->channelsLoaded = false;
    }

    public function getIdByCode(string $code): ?int
    {
        if (false === $this->channelsLoaded) {
            $this->loadChannels();
        }

        return $this->channelIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        if (false === $this->channelsLoaded) {
            $this->loadChannels();
        }

        return $this->channelCodesByIds[$id] ?? null;
    }

    private function loadChannels(): void
    {
        $channels = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_channel;'
        )->fetchOne();

        if ($channels) {
            $this->channelCodesByIds = json_decode($channels, true);
            $this->channelIdsByCodes = array_flip($this->channelCodesByIds);
        }

        $this->channelsLoaded = true;
    }
}
