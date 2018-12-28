<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupPermission;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\FindReferenceEntityPermissionsDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\PermissionDetails;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindReferenceEntityPermissionsDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindReferenceEntityPermissionsDetailsInterface */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->get('akeneo.referencentity.infrastructure.persistence.permission.query.find_reference_entity_permissions_details');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_reference_entity_permissions_details()
    {
        $permissions = ($this->query)(ReferenceEntityIdentifier::fromString('designer'));
        $this->assertPermissions(
            [
                ['user_group_identifier' => 10, 'user_group_name' => 'Catalog Manager', 'right_level' => 'edit'],
                ['user_group_identifier' => 11, 'user_group_name' => 'IT support', 'right_level' => 'view'],
            ],
            $permissions
        );
    }

    /**
     * @test
     */
    public function it_finds_no_permissions_details()
    {
        $permissions = ($this->query)(ReferenceEntityIdentifier::fromString('brand'));
        $this->assertEmpty($permissions);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')
            ->create(
                ReferenceEntity::create(
                    ReferenceEntityIdentifier::fromString('designer'),
                    [],
                    Image::createEmpty()
                )
            );
        $insertFakeGroups = <<<SQL
 INSERT INTO oro_access_group (id, name)
 VALUES
    (10, 'Catalog Manager'),
    (11, 'IT support');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeGroups);

        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
            ]
        );

        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity_permission')->save($referenceEntityPermission);
    }

    private function assertPermissions(
        array $expectedNormalizedPermissionsDetails,
        array $actualPermissionsDetails
    ): void {
        $actualNormalizedPermissionDetails = array_map(
            function (PermissionDetails $permissionDetails) {
                return $permissionDetails->normalize();
            },
            $actualPermissionsDetails
        );
        $this->assertEquals($expectedNormalizedPermissionsDetails, $actualNormalizedPermissionDetails);
    }
}
