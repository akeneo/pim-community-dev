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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\IgnoredTitleSuggestionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Types\Types;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class IgnoredTitleSuggestionRepository implements IgnoredTitleSuggestionRepositoryInterface
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find(ProductId $productId): ?Read\IgnoredTitleSuggestion
    {
        $query = <<<SQL
SELECT product_id, ignored_suggestions
FROM pimee_data_quality_insights_title_formatting_ignore
WHERE product_id = :product_id
SQL;
        $statement = $this->db->executeQuery($query, [
            'product_id' => $productId->toInt(),
        ], [
            'product_id' => Types::INTEGER,
        ]);

        $result = $statement->fetch(FetchMode::ASSOCIATIVE);

        if ($result === null || $result === false) {
            return null;
        }

        return new Read\IgnoredTitleSuggestion(
            new ProductId(intval($result['product_id'])),
            json_decode($result['ignored_suggestions'], true)
        );
    }

    public function save(Write\IgnoredTitleSuggestion $ignoredTitleSuggestion): void
    {
        $channel = strval($ignoredTitleSuggestion->getChannel());
        $locale = strval($ignoredTitleSuggestion->getLocale());

        $ignoredSuggestion = [
            $channel => [
                $locale => strval($ignoredTitleSuggestion->getTitleSuggestion())
            ]
        ];

        $query = <<<SQL
INSERT INTO  pimee_data_quality_insights_title_formatting_ignore (product_id, ignored_suggestions)
VALUES (:product_id, :ignored_suggestions)
ON DUPLICATE KEY UPDATE
    ignored_suggestions = JSON_MERGE_PATCH(ignored_suggestions, :ignored_suggestions)
SQL;

        $this->db->executeUpdate($query,
            [
                'product_id' => $ignoredTitleSuggestion->getProductId()->toInt(),
                'ignored_suggestions' => $ignoredSuggestion,
            ],
            [
                'product_id' => Types::STRING,
                'ignored_suggestions' => Types::JSON,
            ]
        );
    }
}
