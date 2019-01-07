<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityPermissionRepository;
use Akeneo\ReferenceEntity\Domain\Model\Permission\ReferenceEntityPermission;
use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupPermission;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PHPUnit\Framework\TestCase;

class InMemoryReferenceEntityPermissionRepositoryTest extends TestCase
{
    /** @var InMemoryReferenceEntityPermissionRepository */
    private $inMemoryReferenceEntityPermissionRepository;

    public function setup()
    {
        $this->inMemoryReferenceEntityPermissionRepository = new InMemoryReferenceEntityPermissionRepository();
    }

    /**
     * @test
     */
    public function it_saves_a_reference_entity_permission()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $userGroupIdentifier = UserGroupIdentifier::fromInteger(12);
        $rightLevel = RightLevel::fromString('view');
        $referenceEntityPermission = ReferenceEntityPermission::create(
            $referenceEntityIdentifier,
            [UserGroupPermission::create($userGroupIdentifier, $rightLevel)]
        );

        $this->inMemoryReferenceEntityPermissionRepository->save($referenceEntityPermission);

        $this->assertTrue($this->inMemoryReferenceEntityPermissionRepository->hasPermission(
            $referenceEntityIdentifier,
            $userGroupIdentifier,
            $rightLevel
        ));

        $this->assertFalse($this->inMemoryReferenceEntityPermissionRepository->hasPermission(
            $referenceEntityIdentifier,
            $userGroupIdentifier,
            RightLevel::fromString('edit')
        ));
    }

    /**
     * @test
     */
    public function it_returns_the_reference_entity()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedReferenceEntityPermission = ReferenceEntityPermission::create(
            $referenceEntityIdentifier,
            [UserGroupPermission::create(UserGroupIdentifier::fromInteger(12), RightLevel::fromString('view'))]
        );
        $this->inMemoryReferenceEntityPermissionRepository->save($expectedReferenceEntityPermission);

        $actualReferenceEntityPermission = $this->inMemoryReferenceEntityPermissionRepository->getByReferenceEntityIdentifier($referenceEntityIdentifier);

        $this->assertEquals($expectedReferenceEntityPermission, $actualReferenceEntityPermission);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_reference_entity_permission_if_it_has_none()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $actualReferenceEntityPermission = $this->inMemoryReferenceEntityPermissionRepository->getByReferenceEntityIdentifier($referenceEntityIdentifier);
        $expectedReferenceEntityPermission = ReferenceEntityPermission::create($referenceEntityIdentifier, []);

        $this->assertEquals($expectedReferenceEntityPermission->normalize(), $actualReferenceEntityPermission->normalize());
    }
}
