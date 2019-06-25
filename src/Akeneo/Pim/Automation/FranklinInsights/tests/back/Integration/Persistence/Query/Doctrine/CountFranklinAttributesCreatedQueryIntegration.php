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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Query\CountFranklinAttributesCreatedQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CountFranklinAttributesCreatedQueryIntegration extends TestCase
{
    public function test_it_counts_the_number_of_attributes_created(): void
    {
        $this->getConnection()->insert(
            'pimee_franklin_insights_attribute_created',
            [
                'attribute_code' => 'brand',
                'attribute_type' => 'pim_catalog_text',
            ]
        );
        $this->getConnection()->insert(
            'pimee_franklin_insights_attribute_created',
            [
                'attribute_code' => 'color',
                'attribute_type' => 'pim_catalog_simpleselect',
            ]
        );

        $result = $this->getQuery()->execute();

        Assert::assertSame(2, $result);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getQuery(): CountFranklinAttributesCreatedQueryInterface
    {
        return $this->get(
            'akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.count_franklin_attributes_created'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
