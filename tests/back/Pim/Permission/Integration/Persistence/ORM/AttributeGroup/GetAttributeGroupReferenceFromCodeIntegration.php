<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\AttributeGroup;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupReferenceFromCode;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetAttributeGroupReferenceFromCodeIntegration extends TestCase
{
    private GetAttributeGroupReferenceFromCode $query;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(GetAttributeGroupReferenceFromCode::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItReturnsTheAttributeGroupEntity(): void
    {
        $entity = $this->query->execute('other');

        $this->assertNotNull($entity);
        $this->assertInstanceOf(AttributeGroupInterface::class, $entity);
    }

    public function testItReturnsNullWhenTheAttributeGroupCodeIsUnknown(): void
    {
        $entity = $this->query->execute('not_an_existing_attr_group');

        $this->assertNull($entity);
    }
}
