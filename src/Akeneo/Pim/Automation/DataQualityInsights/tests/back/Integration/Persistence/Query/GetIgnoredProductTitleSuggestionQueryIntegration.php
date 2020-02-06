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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetIgnoredProductTitleSuggestionQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Types;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetIgnoredProductTitleSuggestionQueryIntegration extends TestCase
{
    public function test_it_returns_ignored_title_suggestion()
    {
        $this->givenSomeTitleSuggestionsAreIgnored();
        $result = $this
            ->get(GetIgnoredProductTitleSuggestionQuery::class)
            ->execute(new ProductId(1000), new ChannelCode('ecommerce'), new LocaleCode('en_US'));

        $this->assertEquals('My suggested title for ecommerce', $result);
    }

    public function test_it_does_not_return_ignored_title_suggestion_when_locale_is_not_supported()
    {
        $this->givenSomeTitleSuggestionsAreIgnored();
        $result = $this
            ->get(GetIgnoredProductTitleSuggestionQuery::class)
            ->execute(new ProductId(1000), new ChannelCode('ecommerce'), new LocaleCode('it_IT'));

        $this->assertNull($result);
    }

    public function test_it_does_not_return_ignored_title_suggestion_when_product_id_is_wrong()
    {
        $this->givenSomeTitleSuggestionsAreIgnored();
        $result = $this
            ->get(GetIgnoredProductTitleSuggestionQuery::class)
            ->execute(new ProductId(2000), new ChannelCode('ecommerce'), new LocaleCode('en_US'));

        $this->assertNull($result);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenSomeTitleSuggestionsAreIgnored()
    {
        $db = $this->get('database_connection');
        $query = <<<SQL
            INSERT INTO pimee_data_quality_insights_title_formatting_ignore
                (product_id, ignored_suggestions)
            VALUES
                (:product_id, :ignored_suggestions);
SQL;

        $db->executeUpdate($query, [
            'product_id' => 1000,
            'ignored_suggestions' => [
                'ecommerce' => [
                    'en_US' => "My suggested title for ecommerce"
                ],
                'print' => [
                    'en_US' => "My suggested title for print"
                ],
            ],
        ], ['ignored_suggestions' => Types::JSON]);
    }
}
