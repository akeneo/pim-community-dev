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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Doctrine\DBAL\Connection;

class CachedGetLocalesByChannelQuery implements GetLocalesByChannelQueryInterface
{
    /** * @var Connection */
    private $db;

    /** @var null|array */
    private $cachedChannelLocaleArray;

    /** @var null|ChannelLocaleCollection */
    private $cachedChannelLocaleCollection;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getArray(): array
    {
        if (null !== $this->cachedChannelLocaleArray) {
            return $this->cachedChannelLocaleArray;
        }

        $query = <<<SQL
SELECT channel.code AS channelCode, locale.code AS localeCode
FROM pim_catalog_channel_locale
INNER JOIN pim_catalog_channel channel on pim_catalog_channel_locale.channel_id = channel.id
INNER JOIN pim_catalog_locale locale on pim_catalog_channel_locale.locale_id = locale.id
ORDER BY channelCode, localeCode;
SQL;

        $statement = $this->db->executeQuery($query);

        $channelsLocales = [];
        foreach ($statement->fetchAll() as $channelLocale) {
            $channelsLocales[$channelLocale['channelCode']][] = $channelLocale['localeCode'];
        }

        $this->cachedChannelLocaleArray = $channelsLocales;

        return $channelsLocales;
    }

    public function getChannelLocaleCollection(): ChannelLocaleCollection
    {
        if (null === $this->cachedChannelLocaleCollection) {
            $channelsLocales = $this->getArray();
            $this->cachedChannelLocaleCollection = new ChannelLocaleCollection($channelsLocales);
        }

        return $this->cachedChannelLocaleCollection;
    }
}
