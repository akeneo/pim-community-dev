<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201103134233_data_quality_insights_attribute_quality_by_locale extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->connection->executeQuery(<<<SQL
CREATE TABLE IF NOT EXISTS pimee_dqi_attribute_locale_quality (
    attribute_code VARCHAR(100) NOT NULL, 
    locale VARCHAR(20) NOT NULL, 
    quality VARCHAR(20) NOT NULL, 
    PRIMARY KEY(attribute_code, locale)
)
DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC;
SQL
        );

        $this->populateAttributeQualityByLocales();
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function populateAttributeQualityByLocales(): void
    {
        $locales = $this->connection->executeQuery(<<<SQL
SELECT code FROM pim_catalog_locale WHERE is_activated = 1;
SQL
        )->fetchAll(\PDO::FETCH_COLUMN);

        $stmt = $this->connection->executeQuery(<<<SQL
     SELECT attribute_code, result 
     FROM pimee_dqi_attribute_spellcheck AS attribute_spellcheck;
SQL
        );

        $attributeLocaleQualities = [];
        while ($attributeSpellcheck = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $attributeSpellcheckResult = json_decode($attributeSpellcheck['result'], true);
            if (!is_array($attributeSpellcheckResult)) {
                continue;
            }
            foreach ($locales as $locale) {
                $quality = $this->computeAttributeLocaleQuality($attributeSpellcheck['attribute_code'], $attributeSpellcheckResult, $locale);
                $attributeLocaleQualities[] = [
                    'attribute_code' => $attributeSpellcheck['attribute_code'],
                    'locale' => $locale,
                    'quality' => $quality,
                ];

                if (count($attributeLocaleQualities) >= 1000) {
                    $this->saveAttributeLocaleQualities($attributeLocaleQualities);
                    $attributeLocaleQualities = [];
                }
            }
        }

        $this->saveAttributeLocaleQualities($attributeLocaleQualities);
    }

    private function computeAttributeLocaleQuality(string $attributeCode, array $attributeSpellcheckResult, string $locale): string
    {
        $spellcheckToImprove = $attributeSpellcheckResult[$locale] ?? null;

        if (true === $spellcheckToImprove) {
            return 'to_improve';
        }

        $optionsQuality = $this->getAttributeOptionsQualitySummary($attributeCode, $locale);

        if (empty($optionsQuality)) {
            return false === $spellcheckToImprove ? 'good' : 'n_a';
        }

        if ($optionsQuality['nb_to_improve'] > 0) {
            return 'to_improve';
        }

        if ($optionsQuality['nb_good'] > 0 || false === $spellcheckToImprove) {
            return 'good';
        }

        return 'n_a';
    }

    private function getAttributeOptionsQualitySummary(string $attribute, string $locale): array
    {
        $localePath = sprintf('$.%s', $locale);

        $query = <<<SQL
SELECT COUNT(*) AS nb_options,
   SUM(IF(JSON_EXTRACT(result, '$localePath') = true, 1, 0))AS nb_to_improve,
   SUM(IF(JSON_EXTRACT(result, '$localePath') = false, 1, 0))AS nb_good
FROM pimee_dqi_attribute_option_spellcheck
where attribute_code = :attributeCode;
SQL;

        return $this->connection->executeQuery($query, ['attributeCode' => $attribute])->fetch(\PDO::FETCH_ASSOC);
    }

    private function saveAttributeLocaleQualities(array $attributeLocaleQualities): void
    {
        if (empty($attributeLocaleQualities)) {
            return;
        }

        $values = array_map(
            fn ($row) => sprintf("('%s','%s','%s')", $row['attribute_code'], $row['locale'], $row['quality']),
            $attributeLocaleQualities
        );
        $values = implode(',', $values);

        $this->connection->executeQuery(<<<SQL
INSERT IGNORE INTO pimee_dqi_attribute_locale_quality (attribute_code, locale, quality) 
VALUES $values
SQL
        );
    }
}
