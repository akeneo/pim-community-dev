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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesAddedToFamiliesQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CountFranklinAttributesAddedToFamiliesQueryIntegration extends TestCase
{
    public function test_it_counts_the_number_of_attributes_added_to_families(): void
    {
        $this->getConnection()->insert(
            'pimee_franklin_insights_attribute_added_to_family',
            [
                'attribute_code' => 'brand',
                'family_code' => 'router',
            ]
        );
        $this->getConnection()->insert(
            'pimee_franklin_insights_attribute_added_to_family',
            [
                'attribute_code' => 'color',
                'family_code' => 'clothes',
            ]
        );

        $result = $this->getQuery()->execute();

        Assert::assertSame(2, $result);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getQuery(): CountFranklinAttributesAddedToFamiliesQueryInterface
    {
        return $this->get(
            'akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.count_franklin_attributes_added_to_families'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
