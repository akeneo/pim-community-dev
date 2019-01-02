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
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission\SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRightTest extends SqlIntegrationTestCase
{
    /** @var SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->get('akeneo.referencentity.infrastructure.persistence.query.find_reference_entity_where_user_group_is_last_to_have_edit_right');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    function it_finds_the_reference_entities_the_user_group_is_the_last_to_have_edit_permission_on()
    {
        $referenceEntityIdentifiers = ($this->query)(10);
        $this->assertEquals(['color'], $referenceEntityIdentifiers);

        $referenceEntityIdentifiers = ($this->query)(11);
        $this->assertEquals([], $referenceEntityIdentifiers);

        $referenceEntityIdentifiers = ($this->query)(12);
        $this->assertEquals(['city'], $referenceEntityIdentifiers);

        $referenceEntityIdentifiers = ($this->query)(13);
        $this->assertEquals([], $referenceEntityIdentifiers);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $designer = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('designer'), [], Image::createEmpty());
        $color = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('color'), [], Image::createEmpty());
        $brand = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('brand'), [], Image::createEmpty());
        $city = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('city'), [], Image::createEmpty());

        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')->create($designer);
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')->create($color);
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')->create($brand);
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')->create($city);

        $insertFakeUserGroups = <<<SQL
 INSERT INTO oro_access_group (id, name)
 VALUES
    (10, 'Catalog Manager'),
    (11, 'Administrator'),
    (12, 'Security Agent'),
    (13, 'IT support');
SQL;
        $this->get('database_connection')->executeUpdate($insertFakeUserGroups);

        /**
         *  Here is the permission fixture set:
         *
         *      DESIGNER
         *          EDIT = 10, 11, 12
         *          VIEW = 13
         *      COLOR
         *          EDIT = 10
         *          VIEW = 11, 12, 13
         *      CITY
         *          EDIT = 12
         *          VIEW = 10, 11, 13
         *      BRAND
         *          no permission set
         */

        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity_permission')
            ->save($referenceEntityPermission);

        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('color'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity_permission')
            ->save($referenceEntityPermission);

        $referenceEntityPermission = ReferenceEntityPermission::create(
            ReferenceEntityIdentifier::fromString('city'),
            [
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(10),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(11),
                    RightLevel::fromString('view')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(12),
                    RightLevel::fromString('edit')
                ),
                UserGroupPermission::create(
                    UserGroupIdentifier::fromInteger(13),
                    RightLevel::fromString('view')
                ),
            ]
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity_permission')
            ->save($referenceEntityPermission);
    }
}
