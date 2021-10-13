<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class AttributeGroupPermissionsFixturesLoader
{
    private AttributeGroupAccessManager $attributeGroupAccessManager;
    private AttributeGroupRepositoryInterface $attributeGroupRepository;

    public function __construct(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager
    ) {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
    }

    /**
     * @param string[] $attributeGroupCodes
     */
    public function givenTheRightOnAttributeGroupCodes(string $accessLevel, GroupInterface $userGroup, array $attributeGroupCodes): void
    {
        foreach ($attributeGroupCodes as $attributeGroupCode) {
            $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);

            $this->attributeGroupAccessManager->revokeAccess($attributeGroup);
            $this->attributeGroupAccessManager->grantAccess($attributeGroup, $userGroup, $accessLevel);
        }
    }

    public function revokeAttributeGroupPermissions(string $attributeGroupCode): void
    {
        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);
        $this->attributeGroupAccessManager->revokeAccess($attributeGroup);
    }
}
