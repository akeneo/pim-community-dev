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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\FranklinAttributeCreatedRepository;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeCreatedRepositoryIntegration extends TestCase
{
    public function test_it_saves_an_attribute_created_event(): void
    {
        $event = new FranklinAttributeCreated(
            new AttributeCode('color'),
            new AttributeType('pim_catalog_text')
        );
        $this->getRepository()->save($event);

        $sqlQuery = <<<'SQL'
SELECT attribute_code, attribute_type, created
FROM pimee_franklin_insights_attribute_created
SQL;

        $stmt = $this->getDbConnection()->query($sqlQuery);
        $retrievedEvents = $stmt->fetchAll();
        Assert::assertCount(1, $retrievedEvents);

        Assert::assertEquals('color', $retrievedEvents[0]['attribute_code']);
        Assert::assertEquals('pim_catalog_text', $retrievedEvents[0]['attribute_type']);
        Assert::assertNotNull($retrievedEvents[0]['created']);
    }

    private function getRepository(): FranklinAttributeCreatedRepository
    {
        return $this->get('akeneo.pim.automation.franklin_insights.repository.franklin_attribute_created');
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
