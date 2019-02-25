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
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

class SqlReferenceEntityPermissionRepositoryTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityPermissionRepositoryInterface */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity_permission');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_saves_and_returns_a_reference_entity_permission()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityPermission = ReferenceEntityPermission::create(
            $referenceEntityIdentifier,
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

        $this->repository->save($referenceEntityPermission);
        $referenceEntityPermission = $this->repository->getByReferenceEntityIdentifier($referenceEntityIdentifier);

        $this->assertEquals($referenceEntityPermission->normalize(), [
            'reference_entity_identifier' => 'designer',
            'permissions'                 => [
                [
                    'user_group_identifier' => 10,
                    'right_level'           => 'edit',
                ],
                [
                    'user_group_identifier' => 11,
                    'right_level'           => 'view',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function it_returns_a_reference_entity_permission_with_no_user_group_permission_when_there_are_no_permissions_set_for_it()
    {
        $referenceEntityPermission = $this->repository->getByReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString('designer')
        );

        $this->assertEquals($referenceEntityPermission->normalize(), [
            'reference_entity_identifier' => 'designer',
            'permissions'                 => [],
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_permission_for_a_reference_entity_that_does_not_exist()
    {
        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
            ]
        );

        $this->expectException(DBALException::class);
        $this->repository->save($referenceEntityPermission);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_permission_for_a_user_group_that_does_not_exist()
    {
        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(999),
                    RightLevel::fromString('edit')
                ),
            ]
        );

        $this->expectException(DBALException::class);
        $this->repository->save($referenceEntityPermission);
    }

    private function thereShouldBePermissionsInDatabase(array $expectedRows): void
    {
        $stmt = $this->get('database_connection')
            ->executeQuery('SELECT * FROM akeneo_reference_entity_reference_entity_permissions;');
        $actualRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        Assert::assertJsonStringEqualsJsonString(
            json_encode($expectedRows),
            json_encode($actualRows),
            'There should be permissions in the database, but some rows weren\'t found.'
        );
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
    (10, 'TEST GROUP'),
    (11, 'TEST GROUP 2');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeGroups);
    }
}
