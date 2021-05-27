<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryFindUserGroupsForSecurityIdentifier;
use Akeneo\AssetManager\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\AssetManager\Domain\Model\SecurityIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindUserGroupsForSecurityIdentifierTest extends TestCase
{
    private InMemoryFindUserGroupsForSecurityIdentifier $query;

    public function setUp(): void
    {
        parent::setUp();
        $this->query = new InMemoryFindUserGroupsForSecurityIdentifier();
    }

    /**
     * @test
     */
    public function it_saves_and_returns_the_user_groups()
    {
        $this->query->stubWith([
            'julia' => [UserGroupIdentifier::fromInteger(1), UserGroupIdentifier::fromInteger(2)],
        ]);
        $userGroupIdentifiers = $this->query->find(SecurityIdentifier::fromString('julia'));

        $normalizedUserGroupIdentifiers = array_map(
            fn(UserGroupIdentifier $userGroupIdentifier) => $userGroupIdentifier->normalize(),
            $userGroupIdentifiers
        );

        $this->assertEquals([1, 2], $normalizedUserGroupIdentifiers);
    }

    /**
     * @test
     */
    public function it_returns_no_user_group()
    {
        $userGroupIdentifiers = $this->query->find(SecurityIdentifier::fromString('julia'));
        $this->assertEmpty($userGroupIdentifiers);
    }
}
