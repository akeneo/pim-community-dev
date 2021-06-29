<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Dbal;

use Doctrine\DBAL\Statement;
use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class MutableAclProviderTest extends TestCase
{
    /** @var MutableAclProvider */
    private $provider;

    /** @var MockObject */
    private $connection;

    protected function setUp(): void
    {
        $platform = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');
        $this->connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));
        $this->connection->expects($this->any())
            ->method('quote')
            ->will(
                $this->returnCallback(
                    function ($input) {
                        return '\'' . $input . '\'';
                    }
                )
            );

        $strategy = $this->createMock('Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface');

        $this->provider = new MutableAclProvider(
            $this->connection,
            $strategy,
            [
                'sid_table_name' => 'acl_security_identities',
                'oid_table_name' => 'acl_object_identities',
                'class_table_name' => 'acl_object_identities',
                'oid_ancestors_table_name' => 'acl_object_identity_ancestors',
                'entry_table_name' => 'acl_entries',
            ]
        );
    }

    public function testBeginTransaction()
    {
        $this->connection->expects($this->once())
            ->method('beginTransaction');
        $this->provider->beginTransaction();
    }

    public function testCommit()
    {
        $this->connection->expects($this->once())
            ->method('commit');
        $this->provider->commit();
    }

    public function testRollBack()
    {
        $this->connection->expects($this->once())
            ->method('rollBack');
        $this->provider->rollBack();
    }

    /**
     * @dataProvider deleteSecurityIdentityProvider
     */
    public function testDeleteSecurityIdentity(SecurityIdentityInterface $sid, $sql)
    {
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo($sql));
        $this->provider->deleteSecurityIdentity($sid);
    }

    /**
     * @dataProvider updateSecurityIdentityProvider
     */
    public function testUpdateSecurityIdentity(SecurityIdentityInterface $sid, $oldName, $sql)
    {
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo($sql));
        $this->provider->updateSecurityIdentity($sid, $oldName);
    }

    /**
     * @dataProvider updateSecurityIdentityNoChangesProvider
     * @expectedException \InvalidArgumentException
     */
    public function testUpdateSecurityIdentityShouldThrowInvalidArgumentException(
        SecurityIdentityInterface $sid,
        $oldName
    ) {
        $this->provider->updateSecurityIdentity($sid, $oldName);
    }

    public static function deleteSecurityIdentityProvider()
    {
        return [
            [
                new UserSecurityIdentity('test', 'Acme\User'),
                'DELETE FROM acl_security_identities WHERE identifier = \'Acme\User-test\' AND username = 1'
            ],
            [
                new RoleSecurityIdentity('ROLE_TEST'),
                'DELETE FROM acl_security_identities WHERE identifier = \'ROLE_TEST\' AND username = 0'
            ],
        ];
    }

    public static function updateSecurityIdentityProvider()
    {
        return [
            [
                new UserSecurityIdentity('test', 'Acme\User'),
                'old',
                'UPDATE acl_security_identities SET identifier = \'Acme\User-test\' WHERE '
                . 'identifier = \'Acme\User-old\' AND username = 1'
            ],
            [
                new RoleSecurityIdentity('ROLE_TEST'),
                'ROLE_OLD',
                'UPDATE acl_security_identities SET identifier = \'ROLE_TEST\' WHERE '
                . 'identifier = \'ROLE_OLD\' AND username = 0'
            ],
        ];
    }

    public static function updateSecurityIdentityNoChangesProvider()
    {
        return [
            [new UserSecurityIdentity('test_new', 'Acme\User'), 'test'],
            [new RoleSecurityIdentity('ROLE_TEST_NEW'), 'ROLE_TEST'],
        ];
    }

    public function testReorderAcesWhenLoadAcls(): void
    {
        $statement = new class() extends Statement {
            public function __construct()
            {
            }
            public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
            {
                return [
                    [
                        'acl_id' => 1,
                        'object_identifier' => 'id1',
                        'parent_object_identity_id' => null,
                        'entries_inheriting' => '',
                        'class_type' => 'type1',
                        'ace_id' => 4,
                        'object_identity_id' => null,
                        'field_name' => null,
                        'ace_order' => 2, // Here the ace_order is important for the test.
                        'mask' => 0,
                        'granting' => 1,
                        'granting_strategy' => 'all',
                        'audit_success' => 0,
                        'audit_failure' => 0,
                        'username' => 'ROLE',
                        'security_identifier' => 'ROLE-ROLE',
                    ],
                ];
            }
        };

        $this->connection->expects($this->any())
            ->method('executeQuery')->willReturn($statement);

        $oids = [new ObjectIdentity('id1', 'type1')];
        $sids = [new RoleSecurityIdentity('ROLE')];

        $results = $this->provider->findAcls($oids, $sids);
        self::assertInstanceOf(\SplObjectStorage::class, $results);
        $acl = $results->offsetGet($oids[0]);
        self::assertInstanceOf(Acl::class, $acl);
        $classAces = $acl->getClassAces();
        self::assertIsArray($classAces);

        // the ace_order is 2 but the key must be 0 because of the re-order
        self::assertArrayHasKey(0, $classAces);
        self::assertArrayNotHasKey(2, $classAces);
    }
}
