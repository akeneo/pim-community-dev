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

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20201103134233_data_quality_insights_attribute_quality_by_locale_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20201103134233_data_quality_insights_attribute_quality_by_locale';

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_populates_attribute_locale_qualities(): void
    {
        $this->resetAttributesSpellchecks();
        $this->activateLocales(['en_US', 'fr_FR', 'de_DE']);

        /*
         *  Matrix of expected results
         *
         * Attribute code | Locale | Attribute spellcheck | Options Spellcheck | Quality
         * ---------------|--------|--------------------------------------------------------
         * name           | en_US  | good                 | no options         | good
         * name           | fr_FR  | to_improve           | no options         | to_improve
         * name           | de_DE  | n_a                  | no options         | n_a
         * color          | en_US  | good                 | good               | good
         * color          | fr_FR  | good                 | to_improve         | to_improve
         * color          | de_DE  | good                 | n_a                | good
         * material       | en_US  | to_improve           | good               | to_improve
         * material       | fr_FR  | to_improve           | to_improve         | to_improve
         * material       | de_DE  | to_improve           | n_a                | to_improve
         * type           | en_US  | n_a                  | good               | good
         * type           | fr_FR  | n_a                  | to_improve         | to_improve
         * type           | de_DE  | n_a                  | n_a                | n_a
         */
        $expectedAttributesLocalesQuality = [
            'name' => ['en_US' => 'good', 'fr_FR' => 'to_improve', 'de_DE' => 'n_a'],
            'color' => ['en_US' => 'good', 'fr_FR' => 'to_improve', 'de_DE' => 'good'],
            'material' => ['en_US' => 'to_improve', 'fr_FR' => 'to_improve', 'de_DE' => 'to_improve'],
            'type' => ['en_US' => 'good', 'fr_FR' => 'to_improve', 'de_DE' => 'n_a'],
        ];

        $this->saveAttributesSpellcheck([
            'name' => [
                'to_improve' => true,
                'result' => ['en_US' => false, 'fr_FR' => true],
            ],
            'color' => [
                'to_improve' => false,
                'result' => ['en_US' => false, 'fr_FR' => false, 'de_DE' => false],
            ],
            'material' => [
                'to_improve' => true,
                'result' => ['en_US' => true, 'fr_FR' => true, 'de_DE' => true],
            ],
            'type' => [
                'to_improve' => null,
                'result' => [],
            ],
        ]);

        $this->saveAttributesOptionsSpellcheck(['color', 'material', 'type'], [
            'option_A' => ['to_improve' => false, 'result' => ['en_US' => false, 'fr_FR' => false]],
            'option_B' => ['to_improve' => true, 'result' => ['en_US' => false, 'fr_FR' => true]],
            'option_C' => ['to_improve' => null, 'result' => []],
        ]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $results = $this->get('database_connection')->executeQuery(<<<SQL
SELECT attribute_code, JSON_OBJECTAGG(locale, quality) AS locales_quality
FROM pimee_dqi_attribute_locale_quality
GROUP BY attribute_code;
SQL
        )->fetchAll(\PDO::FETCH_ASSOC);

        $attributesLocalesQuality = [];
        foreach ($results as $result) {
            $attributesLocalesQuality[$result['attribute_code']] = json_decode($result['locales_quality'], true);
        }

        $this->assertEquals($expectedAttributesLocalesQuality, $attributesLocalesQuality);
    }

    private function saveAttributesSpellcheck(array $attributesSpellcheck): void
    {
        $values = [];
        foreach ($attributesSpellcheck as $attributeCode => $spellcheck) {
            $toImprove = null === $spellcheck['to_improve'] ? 'null' : intval($spellcheck['to_improve']);
            $values[] = sprintf("('%s', NOW(), %s, '%s')", $attributeCode, $toImprove, json_encode($spellcheck['result']));
        }
        $values = implode(',', $values);

        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO pimee_dqi_attribute_spellcheck (attribute_code, evaluated_at, to_improve, result) 
VALUES $values
SQL
        );
    }

    private function saveAttributesOptionsSpellcheck(array $attributeCodes, array $optionsSpellchecks): void
    {
        $values = [];
        foreach ($attributeCodes as $attributeCode) {
            foreach ($optionsSpellchecks as $optionCode => $optionsSpellcheck) {
                $toImprove = null === $optionsSpellcheck['to_improve'] ? 'null' : intval($optionsSpellcheck['to_improve']);
                $values[] = sprintf("('%s', '%s', NOW(), %s, '%s')", $attributeCode, $optionCode, $toImprove, json_encode($optionsSpellcheck['result']));
            }
        }
        $values = implode(',', $values);

        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO pimee_dqi_attribute_option_spellcheck (attribute_code, attribute_option_code, evaluated_at, to_improve, result) 
VALUES $values
SQL
        );
    }

    private function activateLocales(array $locales): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
UPDATE pim_catalog_locale SET is_activated = 1 WHERE code IN (:codes)
SQL,
            ['codes' => $locales],
            ['codes' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function resetAttributesSpellchecks(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pimee_dqi_attribute_locale_quality;

DROP TABLE IF EXISTS pimee_dqi_attribute_spellcheck;
CREATE TABLE pimee_dqi_attribute_spellcheck (
    attribute_code VARCHAR(100) NOT NULL PRIMARY KEY, 
    evaluated_at DATETIME NOT NULL, 
    to_improve TINYINT(1) DEFAULT NULL, 
    result JSON NOT NULL, 
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS pimee_dqi_attribute_option_spellcheck;
CREATE TABLE IF NOT EXISTS pimee_dqi_attribute_option_spellcheck (
    attribute_code VARCHAR(100) NOT NULL,
    attribute_option_code VARCHAR(100) NOT NULL,
    evaluated_at DATETIME NOT NULL,
    to_improve TINYINT NULL,
    result JSON NOT NULL,
    PRIMARY KEY attribute_option_key (attribute_code, attribute_option_code),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }
}
