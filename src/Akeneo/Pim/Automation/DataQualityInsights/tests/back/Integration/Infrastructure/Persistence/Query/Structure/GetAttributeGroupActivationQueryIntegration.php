<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeGroupActivationQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetAttributeGroupActivationQueryIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_an_attribute_group_activation_by_its_code()
    {
        $repository = $this->get(AttributeGroupActivationRepository::class);
        $query = $this->get(GetAttributeGroupActivationQuery::class);

        $marketing = new AttributeGroupCode('marketing');
        $marketingActivation = new AttributeGroupActivation($marketing, false);
        $repository->save($marketingActivation);

        $technical = new AttributeGroupCode('technical');
        $technicalActivation = new AttributeGroupActivation($technical, true);
        $repository->save($technicalActivation);

        $this->assertEquals($marketingActivation, $query->byCode($marketing));
        $this->assertEquals($technicalActivation, $query->byCode($technical));
        $this->assertNull($query->byCode(new AttributeGroupCode('design')));
    }
}
