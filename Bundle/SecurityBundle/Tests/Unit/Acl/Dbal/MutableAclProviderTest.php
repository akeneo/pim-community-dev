<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Dbal;

use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class MutableAclProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var MutableAclProvider */
    private $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $connection;

    protected function setUp()
    {
        $platform = $this->getMockForAbstractClass('Doctrine\DBAL\Platforms\AbstractPlatform');
        $platform->expects($this->any())
            ->method('convertBooleans')
            ->will(
                $this->returnValueMap(
                    array(
                        array(false, '0'),
                        array(true, '1'),
                    )
                )
            );
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

        $strategy = $this->getMock('Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface');

        $this->provider = new MutableAclProvider(
            $this->connection,
            $strategy,
            array('sid_table_name' => 'acl_security_identities')
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
        return array(
            array(
                new UserSecurityIdentity('test', 'Acme\User'),
                'DELETE FROM acl_security_identities WHERE identifier = \'Acme\User-test\' AND username = 1'
            ),
            array(
                new RoleSecurityIdentity('ROLE_TEST'),
                'DELETE FROM acl_security_identities WHERE identifier = \'ROLE_TEST\' AND username = 0'
            ),
        );
    }

    public static function updateSecurityIdentityProvider()
    {
        return array(
            array(
                new UserSecurityIdentity('test', 'Acme\User'),
                'old',
                'UPDATE acl_security_identities SET identifier = \'Acme\User-test\' WHERE '
                . 'identifier = \'Acme\User-old\' AND username = 1'
            ),
            array(
                new RoleSecurityIdentity('ROLE_TEST'),
                'ROLE_OLD',
                'UPDATE acl_security_identities SET identifier = \'ROLE_TEST\' WHERE '
                . 'identifier = \'ROLE_OLD\' AND username = 0'
            ),
        );
    }

    public static function updateSecurityIdentityNoChangesProvider()
    {
        return array(
            array(new UserSecurityIdentity('test', 'Acme\User'), 'test'),
            array(new RoleSecurityIdentity('ROLE_TEST'), 'ROLE_TEST'),
        );
    }
}
