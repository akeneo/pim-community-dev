<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeGroupActivationQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class UpdateAttributeGroupActivationSubscriberIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_new_attribute_group_activation()
    {
        $this->createAttributeGroup('dimension');

        $attributeGroupActivation = $this->get(GetAttributeGroupActivationQuery::class)->byCode(new AttributeGroupCode('dimension'));

        $this->assertNotNull($attributeGroupActivation);
        $this->assertTrue($attributeGroupActivation->isActivated());
    }

    public function test_it_removes_attribute_group_activation()
    {
        $this->createAttributeGroup('dimension');
        $this->removeAttributeGroup('dimension');

        $attributeGroupActivation = $this->get(GetAttributeGroupActivationQuery::class)->byCode(new AttributeGroupCode('dimension'));
        $this->assertNull($attributeGroupActivation);
    }

    private function createAttributeGroup(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
    }

    private function removeAttributeGroup(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier($code);
        $this->assertNotNull($attributeGroup);

        $this->get('pim_catalog.remover.attribute_group')->remove($attributeGroup);
    }
}
