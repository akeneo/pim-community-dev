<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Doctrine\DBAL\Connection;

class GetLocalesByChannelQuery implements GetLocalesByChannelQueryInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function execute(): array
    {
        $query = <<<SQL
SELECT channel.code AS channelCode, locale.code AS localeCode
FROM pim_catalog_channel_locale
INNER JOIN pim_catalog_channel channel on pim_catalog_channel_locale.channel_id = channel.id
INNER JOIN pim_catalog_locale locale on pim_catalog_channel_locale.locale_id = locale.id
ORDER BY channelCode, localeCode;
SQL;

        $statement = $this->db->executeQuery($query);

        $results = [];
        foreach ($statement->fetchAll() as $channelLocale) {
            $results[$channelLocale['channelCode']][] = $channelLocale['localeCode'];
        }

        return $results;
    }

    public function getChannelLocaleCollection(): ChannelLocaleCollection
    {
        $channelsLocales = $this->execute();

        return new ChannelLocaleCollection($channelsLocales);
    }
}
