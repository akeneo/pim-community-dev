<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\AttributeGroup;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupsAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;

class GetAttributeGroupsAccessesWithHighestLevelIntegration extends TestCase
{
    private GetAttributeGroupsAccessesWithHighestLevel $query;
    private GroupRepositoryInterface $groupRepository;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(GetAttributeGroupsAccessesWithHighestLevel::class);
        $this->groupRepository = self::getContainer()->get('pim_user.repository.group');
    }

    public function testItFetchesAttributeGroupsWithHighestAccessLevel(): void
    {
        $expected = [
            'other' => Attributes::EDIT_ATTRIBUTES,
        ];

        $groupId = $this->groupRepository->findOneByIdentifier('redactor')->getId();
        $results = $this->query->execute($groupId);

        $this->assertSame($expected, $results);
    }
}
