<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Extension\NullAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AceManipulationHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class AceManipulationHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var AceManipulationHelper */
    private $manipulator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $acl;

    protected function setUp(): void
    {
        $this->acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->manipulator = new AceManipulationHelper();
    }

    /**
     * @dataProvider aceTypesProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetPermissionShouldCallUpdateAceForAce3($type, $field)
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $replace = true;
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $aceSid1 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceGranting1 = $granting;
        $aceMask1 = $mask;
        $aceStrategy1 = $strategy;

        $aceSid2 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $aceSid3 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceGranting3 = $granting;
        $aceMask3 = 789;
        $aceStrategy3 = $strategy;

        $ace1 = $this->getAce($aceSid1, $aceGranting1, $aceMask1, $aceStrategy1);
        $ace2 = $this->getAce($aceSid2);
        $ace3 = $this->getAce($aceSid3, $aceGranting3, $aceMask3, $aceStrategy3, 2, 0);

        $sid->expects($this->at(0))
            ->method('equals')
            ->with($this->identicalTo($aceSid1))
            ->will($this->returnValue(true));
        $sid->expects($this->at(1))
            ->method('equals')
            ->with($this->identicalTo($aceSid2))
            ->will($this->returnValue(false));
        $sid->expects($this->at(2))
            ->method('equals')
            ->with($this->identicalTo($aceSid3))
            ->will($this->returnValue(true));

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'Aces')
                ->will($this->returnValue([$ace1, $ace2, $ace3]));
        } else {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'FieldAces')
                ->with($this->equalTo($field))
                ->will($this->returnValue([$ace1, $ace2, $ace3]));
        }

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('update' . $type . 'Ace')
                ->with(
                    $this->equalTo(2),
                    $this->equalTo($mask),
                    $this->equalTo($strategy)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('update' . $type . 'FieldAce')
                ->with(
                    $this->equalTo(2),
                    $this->equalTo($field),
                    $this->equalTo($mask),
                    $this->equalTo($strategy)
                );
        }
        $this->acl->expects($this->never())
            ->method('insert' . $type . 'Ace');
        $this->acl->expects($this->never())
            ->method('insert' . $type . 'FieldAce');

        $this->assertTrue(
            $this->manipulator->setPermission(
                $this->acl,
                new NullAclExtension(),
                $replace,
                $type,
                $field,
                $sid,
                $granting,
                $mask,
                $strategy
            )
        );
    }

    /**
     * @dataProvider aceTypesProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetPermissionShouldCallInsertAce($type, $field)
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $replace = false;
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $aceSid1 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $aceSid2 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceGranting2 = $granting;
        $aceMask2 = $mask;
        $aceStrategy2 = 'all';

        $ace1 = $this->getAce($aceSid1);
        $ace2 = $this->getAce($aceSid2, $aceGranting2, $aceMask2, $aceStrategy2);

        $sid->expects($this->at(0))
            ->method('equals')
            ->with($this->identicalTo($aceSid1))
            ->will($this->returnValue(false));
        $sid->expects($this->at(1))
            ->method('equals')
            ->with($this->identicalTo($aceSid2))
            ->will($this->returnValue(true));

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'Aces')
                ->will($this->returnValue([$ace1, $ace2]));
        } else {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'FieldAces')
                ->with($this->equalTo($field))
                ->will($this->returnValue([$ace1, $ace2]));
        }

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('insert' . $type . 'Ace')
                ->with(
                    $this->identicalTo($sid),
                    $this->equalTo($mask),
                    $this->equalTo(0),
                    $this->equalTo($granting),
                    $this->equalTo($strategy)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('insert' . $type . 'FieldAce')
                ->with(
                    $this->equalTo($field),
                    $this->identicalTo($sid),
                    $this->equalTo($mask),
                    $this->equalTo(0),
                    $this->equalTo($granting),
                    $this->equalTo($strategy)
                );
        }
        $this->acl->expects($this->never())
            ->method('update' . $type . 'Ace');
        $this->acl->expects($this->never())
            ->method('update' . $type . 'FieldAce');

        $this->assertTrue(
            $this->manipulator->setPermission(
                $this->acl,
                new NullAclExtension(),
                $replace,
                $type,
                $field,
                $sid,
                $granting,
                $mask,
                $strategy
            )
        );
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testDeletePermission($type, $field)
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $aceSid1 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceGranting1 = true;
        $aceMask1 = 123;
        $aceStrategy1 = 'equal';

        $aceSid2 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $aceSid3 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceGranting3 = $granting;
        $aceMask3 = $mask;
        $aceStrategy3 = $strategy;

        $ace1 = $this->getAce($aceSid1, $aceGranting1, $aceMask1, $aceStrategy1);
        $ace2 = $this->getAce($aceSid2);
        $ace3 = $this->getAce($aceSid3, $aceGranting3, $aceMask3, $aceStrategy3);

        $sid->expects($this->at(0))
            ->method('equals')
            ->with($this->identicalTo($aceSid1))
            ->will($this->returnValue(true));
        $sid->expects($this->at(1))
            ->method('equals')
            ->with($this->identicalTo($aceSid2))
            ->will($this->returnValue(false));
        $sid->expects($this->at(2))
            ->method('equals')
            ->with($this->identicalTo($aceSid3))
            ->will($this->returnValue(true));

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'Aces')
                ->will($this->returnValue([$ace1, $ace2, $ace3]));
        } else {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'FieldAces')
                ->with($this->equalTo($field))
                ->will($this->returnValue([$ace1, $ace2, $ace3]));
        }
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'Ace')
                ->with(
                    $this->equalTo(2)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'FieldAce')
                ->with(
                    $this->equalTo(2),
                    $this->equalTo($field)
                );
        }

        $this->assertTrue(
            $this->manipulator->deletePermission($this->acl, $type, $field, $sid, $granting, $mask, $strategy)
        );
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testDeleteAllPermissions($type, $field)
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $aceSid1 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $aceSid2 = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $ace1 = $this->getAce($aceSid1);
        $ace2 = $this->getAce($aceSid2);

        $sid->expects($this->at(0))
            ->method('equals')
            ->with($this->identicalTo($aceSid1))
            ->will($this->returnValue(true));
        $sid->expects($this->at(1))
            ->method('equals')
            ->with($this->identicalTo($aceSid2))
            ->will($this->returnValue(false));

        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'Aces')
                ->will($this->returnValue([$ace1, $ace2]));
        } else {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'FieldAces')
                ->with($this->equalTo($field))
                ->will($this->returnValue([$ace1, $ace2]));
        }
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'Ace')
                ->with(
                    $this->equalTo(0)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'FieldAce')
                ->with(
                    $this->equalTo(0),
                    $this->equalTo($field)
                );
        }

        $this->assertTrue($this->manipulator->deleteAllPermissions($this->acl, $type, $field, $sid));
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testGetAces($type, $field)
    {
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'Aces')
                ->will($this->returnValue([]));
        } else {
            $this->acl->expects($this->once())
                ->method('get' . $type . 'FieldAces')
                ->with($this->equalTo($field))
                ->will($this->returnValue([]));
        }

        $this->assertEquals(
            [],
            $this->manipulator->getAces($this->acl, $type, $field)
        );
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testInsertAce($type, $field)
    {
        $index = 1;
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('insert' . $type . 'Ace')
                ->with(
                    $this->identicalTo($sid),
                    $this->equalTo($mask),
                    $this->equalTo($index),
                    $this->equalTo($granting),
                    $this->equalTo($strategy)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('insert' . $type . 'FieldAce')
                ->with(
                    $this->equalTo($field),
                    $this->identicalTo($sid),
                    $this->equalTo($mask),
                    $this->equalTo($index),
                    $this->equalTo($granting),
                    $this->equalTo($strategy)
                );
        }

        $this->manipulator->insertAce($this->acl, $type, $field, $index, $sid, $granting, $mask, $strategy);
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testUpdateAce($type, $field)
    {
        $index = 1;
        $mask = 123;
        $strategy = 'any';
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('update' . $type . 'Ace')
                ->with(
                    $this->equalTo($index),
                    $this->equalTo($mask),
                    $this->equalTo($strategy)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('update' . $type . 'FieldAce')
                ->with(
                    $this->equalTo($index),
                    $this->equalTo($field),
                    $this->equalTo($mask),
                    $this->equalTo($strategy)
                );
        }

        $this->manipulator->updateAce($this->acl, $type, $field, $index, $mask, $strategy);
    }

    /**
     * @dataProvider aceTypesProvider
     */
    public function testDeleteAce($type, $field)
    {
        $index = 1;
        $mask = 123;
        $strategy = 'any';
        if ($field === null) {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'Ace')
                ->with(
                    $this->equalTo($index)
                );
        } else {
            $this->acl->expects($this->once())
                ->method('delete' . $type . 'FieldAce')
                ->with(
                    $this->equalTo($index),
                    $this->equalTo($field)
                );
        }

        $this->manipulator->deleteAce($this->acl, $type, $field, $index, $mask, $strategy);
    }

    public static function aceTypesProvider()
    {
        return [
            [AclManager::CLASS_ACE, null],
            [AclManager::OBJECT_ACE, null],
            [AclManager::CLASS_ACE, 'SomeField'],
            [AclManager::OBJECT_ACE, 'SomeField'],
        ];
    }

    private function getAce(
        $sid,
        $granting = null,
        $mask = null,
        $strategy = null,
        $getMaskCallCount = 1,
        $getStrategyCallCount = 1
    ) {
        $ace = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $ace->expects($this->once())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid));
        if ($granting !== null) {
            $ace->expects($this->once())
                ->method('isGranting')
                ->will($this->returnValue($granting));
        }
        if ($mask !== null) {
            $ace->expects($this->exactly($getMaskCallCount))
                ->method('getMask')
                ->will($this->returnValue($mask));
        }
        if ($strategy !== null) {
            $ace->expects($this->exactly($getStrategyCallCount))
                ->method('getStrategy')
                ->will($this->returnValue($strategy));
        }

        return $ace;
    }
}
