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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Locale;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection
    {
        $activatedLocaleCodes = [];
        if (!$localeIdentifiers->isEmpty()) {
            $activatedLocaleCodes = $this->fetchActivatedLocaleCodesFromIdentifiers($localeIdentifiers);
        }

        return LocaleIdentifierCollection::fromNormalized($activatedLocaleCodes);
    }

    /**
     * @return string[]
     */
    private function fetchActivatedLocaleCodesFromIdentifiers(LocaleIdentifierCollection $localeIdentifiers): array
    {
        $query = <<<SQL
          SELECT code
          FROM pim_catalog_locale 
          WHERE is_activated = 1 AND code IN (:locale_codes)
SQL;

        $statement = $this->sqlConnection->executeQuery($query, [
            'locale_codes' => $localeIdentifiers->normalize(),
        ], [
            'locale_codes' => Connection::PARAM_STR_ARRAY,
        ]);

        $platform = $this->sqlConnection->getDatabasePlatform();
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn ($result) => Type::getType(Type::STRING)->convertToPhpValue($result['code'], $platform), $results);
    }
}
