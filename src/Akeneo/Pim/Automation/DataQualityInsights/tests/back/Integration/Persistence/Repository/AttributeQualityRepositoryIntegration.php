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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeQualityRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class AttributeQualityRepositoryIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_saves_an_attribute_quality()
    {
        $attributeCode = new AttributeCode('color');
        $quality = Quality::good();

        $this->get(AttributeQualityRepository::class)->save($attributeCode, $quality);
        $this->assertAttributeQualityExists($attributeCode, $quality);

        $updatedQuality = Quality::toImprove();
        $this->get(AttributeQualityRepository::class)->save($attributeCode, $updatedQuality);
        $this->assertAttributeQualityExists($attributeCode, $updatedQuality);
    }

    private function assertAttributeQualityExists(AttributeCode $attributeCode, Quality $quality): void
    {
        $query = <<<SQL
SELECT 1
FROM pimee_dqi_attribute_quality
WHERE attribute_code = :attributeCode AND quality = :quality
SQL;

        $attributeQualityExists = (bool) $this->get('database_connection')->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'quality' => $quality
        ])->fetchColumn();

        $this->assertTrue($attributeQualityExists, sprintf('Attribute "%s" with quality "%s" should exist', $attributeCode, $quality));
    }
}
