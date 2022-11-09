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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\StructureEvaluation;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Webmozart\Assert\Assert;

final class ConsolidateAttributeQualityIntegration extends DataQualityInsightsTestCase
{
    public function test_quality_is_consolidated_when_an_attribute_is_updated()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);

        $name = $this->createAttribute('name', ['type' => AttributeTypes::TEXT]);

        $this->assertEquals('n_a', $this->getGlobalQuality('name'));
        $this->assertEquals(['en_US' => 'n_a', 'fr_FR' => 'n_a'], $this->getLocalesQuality('name'));

        $this->updateAttribute($name, ['labels' => [
            'en_US' => 'Name',
            'fr_FR' => 'Noommmm'
        ]]);

        $this->assertEquals('to_improve', $this->getGlobalQuality('name'));
        $this->assertEquals(['en_US' => 'good', 'fr_FR' => 'to_improve'], $this->getLocalesQuality('name'));
    }

    private function getGlobalQuality(string $attributeCode): ?string
    {
        $quality = $this->get('database_connection')->executeQuery(<<<SQL
SELECT quality FROM pimee_dqi_attribute_quality
WHERE attribute_code = :attributeCode;
SQL,
            ['attributeCode' => $attributeCode]
        )->fetchColumn();

        return is_string($quality) ? $quality : null;
    }

    private function getLocalesQuality(string $attributeCode): array
    {
        $localesQuality = $this->get('database_connection')->executeQuery(<<<SQL
SELECT JSON_OBJECTAGG(locale, quality)
FROM pimee_dqi_attribute_locale_quality
WHERE attribute_code = :attributeCode;
SQL,
            ['attributeCode' => $attributeCode]
        )->fetchColumn();

        return $localesQuality ? json_decode($localesQuality, true) : [];
    }

    private function updateAttribute(AttributeInterface $attribute, array $data): void
    {
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $errors = $this->get('validator')->validate($attribute);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
