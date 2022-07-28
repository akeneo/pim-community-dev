<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\AttributeGroup;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAllAttributeGroupCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetAllAttributeGroupsCodesIntegration extends TestCase
{
    private GetAllAttributeGroupCodes $query;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(GetAllAttributeGroupCodes::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItFetchesAllAttributeGroupsByCode(): void
    {
        $expected = [
            'other',
        ];

        $results = $this->query->execute();

        $this->assertEqualsCanonicalizing($expected, $results);
    }
}
