<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindUserGroupsForSecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindUserGroupsForSecurityIdentifierTest extends TestCase
{
    /** @var InMemoryFindUserGroupsForSecurityIdentifier */
    private $query;

    public function setup()
    {
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
        $userGroupIdentifiers = ($this->query)(SecurityIdentifier::fromString('julia'));

        $normalizedUserGroupIdentifiers = array_map(
            function (UserGroupIdentifier $userGroupIdentifier) {
                return $userGroupIdentifier->normalize();
            },
            $userGroupIdentifiers
        );

        $this->assertEquals([1, 2], $normalizedUserGroupIdentifiers);
    }

    /**
     * @test
     */
    public function it_returns_no_user_group()
    {
        $userGroupIdentifiers = ($this->query)(SecurityIdentifier::fromString('julia'));
        $this->assertEmpty($userGroupIdentifiers);
    }
}
