<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\FranklinAttributeAddedToFamilyRepository;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\FranklinAttributeCreatedRepository;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeAddedToFamilyRepositoryIntegration extends TestCase
{
    public function test_it_saves_an_attribute_added_to_family_event(): void
    {
        $event = new FranklinAttributeAddedToFamily(
            new AttributeCode('color'),
            new FamilyCode('camcorders')
        );
        $this->getRepository()->save($event);

        $sqlQuery = <<<'SQL'
SELECT attribute_code, family_code, created
FROM pimee_franklin_insights_attribute_added_to_family
SQL;

        $stmt = $this->getDbConnection()->query($sqlQuery);
        $retrievedEvents = $stmt->fetchAll();
        Assert::assertCount(1, $retrievedEvents);

        Assert::assertEquals('color', $retrievedEvents[0]['attribute_code']);
        Assert::assertEquals('camcorders', $retrievedEvents[0]['family_code']);
        Assert::assertNotNull($retrievedEvents[0]['created']);
    }

    public function test_it_counts_attributes_added_to_family_events(): void
    {
        $this->insertAttributeAddedToFamily('color', 'camcorders');
        $this->insertAttributeAddedToFamily('bandwidth', 'routers');
        $this->insertAttributeAddedToFamily('secondary_color', 'camcorders');

        Assert::assertEquals(3, $this->getRepository()->count());
    }

    private function insertAttributeAddedToFamily(string $attributeCode, string $familyCode)
    {
        $event = new FranklinAttributeAddedToFamily(
            new AttributeCode($attributeCode),
            new FamilyCode($familyCode)
        );
        $this->getRepository()->save($event);
    }

    private function getRepository(): FranklinAttributeAddedToFamilyRepository
    {
        return $this->get('akeneo.pim.automation.franklin_insights.repository.franklin_attribute_added_to_family');
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
