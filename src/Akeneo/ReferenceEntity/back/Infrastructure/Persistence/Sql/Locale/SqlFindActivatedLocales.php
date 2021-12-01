<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Locale;

use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocales implements FindActivatedLocalesInterface
{
    public function __construct(private Connection $sqlConnection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $query = <<<SQL
SELECT l.code AS locales_codes
FROM pim_catalog_channel c INNER JOIN pim_catalog_channel_locale cl on c.id = cl.channel_id 
INNER JOIN pim_catalog_locale l ON cl.locale_id = l.id
WHERE l.is_activated = 1
GROUP BY l.code
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $results = $statement->fetchAllAssociative();
        $platform = $this->sqlConnection->getDatabasePlatform();

        return array_map(static fn (array $row) => Type::getType(Types::STRING)->convertToPHPValue(
            $row['locales_codes'],
            $platform
        ), $results ?? []);
    }
}
