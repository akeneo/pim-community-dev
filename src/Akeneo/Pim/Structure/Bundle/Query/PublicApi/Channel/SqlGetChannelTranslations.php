<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Channel;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Doctrine\DBAL\Connection;

class SqlGetChannelTranslations implements GetChannelTranslations
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byLocale(string $locale): array
    {
        $sql = <<<SQL
SELECT c.code, ct.label
FROM pim_catalog_channel c 
JOIN pim_catalog_channel_translation ct ON c.id = ct.foreign_key
WHERE ct.locale = :locale;
SQL;

        $rows = $this->connection->executeQuery($sql, ['locale' => $locale])->fetchAll();

        $channelTranslations = [];
        foreach ($rows as $row) {
            $channelTranslations[$row['code']] = $row['label'];
        }

        return $channelTranslations;
    }
}
