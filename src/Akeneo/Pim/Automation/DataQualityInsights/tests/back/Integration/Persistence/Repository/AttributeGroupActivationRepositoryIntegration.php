<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class AttributeGroupActivationRepositoryIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_saves_an_attribute_group_activation(): void
    {
        $repository = $this->get(AttributeGroupActivationRepository::class);

        $marketing = new AttributeGroupCode('marketing');
        $attributeGroupActivation = new AttributeGroupActivation($marketing, true);

        $repository->save($attributeGroupActivation);
        $this->assertAttributeGroupActivationExists($attributeGroupActivation);

        $updatedAttributeGroupActivation = new AttributeGroupActivation($marketing, false);
        $repository->save($updatedAttributeGroupActivation);
        $this->assertAttributeGroupActivationExists($updatedAttributeGroupActivation);
    }

    private function assertAttributeGroupActivationExists(AttributeGroupActivation $attributeGroupActivation): void
    {
        $query = <<<SQL
SELECT 1 FROM pim_data_quality_insights_attribute_group_activation
WHERE attribute_group_code = :attributeGroupCode AND activated = :activated;
SQL;

        $attributeGroupActivationExists = (bool) $this->get('database_connection')->executeQuery(
            $query,
            [
                'attributeGroupCode' => $attributeGroupActivation->getAttributeGroupCode(),
                'activated' => $attributeGroupActivation->isActivated(),
            ],
            [
                'attributeGroupCode' => \PDO::PARAM_STR,
                'activated' => \PDO::PARAM_BOOL,
            ]
        )->fetchColumn();

        $this->assertTrue($attributeGroupActivationExists);
    }
}
