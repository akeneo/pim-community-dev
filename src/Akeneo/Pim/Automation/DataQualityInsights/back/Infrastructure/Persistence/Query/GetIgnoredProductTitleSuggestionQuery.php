<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetIgnoredProductTitleSuggestionQuery implements GetIgnoredProductTitleSuggestionQueryInterface
{
    private $db;

    /** @var string */
    private $tableName;

    public function __construct(Connection $dbConnection, string $tableName)
    {
        $this->db = $dbConnection;
        $this->tableName = $tableName;
    }

    public function execute(ProductId $productId, ChannelCode $channel, LocaleCode $locale): ?string
    {
        $tableName = $this->tableName;

        $query = <<<SQL
SELECT JSON_UNQUOTE(JSON_EXTRACT(ignored_suggestions, CONCAT('$.', :channel, '.', :locale)))
FROM $tableName
WHERE product_id = :product_id
SQL;

        $statement = $this->db->executeQuery($query, [
            'product_id' => $productId->toInt(),
            'channel' => strval($channel),
            'locale' => strval($locale),
        ], [
            'product_id' => Types::INTEGER,
            'channel' => Types::STRING,
            'locale' => Types::STRING,
        ]);

        $suggestedTitle = $statement->fetchColumn();

        if ($suggestedTitle === null || $suggestedTitle === false) {
            return null;
        }

        return (string) ($suggestedTitle);
    }
}
