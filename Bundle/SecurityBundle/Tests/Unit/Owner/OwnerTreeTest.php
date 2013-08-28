<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\OwnerTree;

class OwnerTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider addBusinessUnitRelationProvider
     */
    public function testAddBusinessUnitRelation($src, $expected)
    {
        $tree = new OwnerTree();
        foreach ($src as $item) {
            $tree->addBusinessUnitRelation($item[0], $item[1]);
        }

        foreach ($expected as $buId => $sBuIds) {
            $this->assertEquals(
                $sBuIds,
                $tree->getSubordinateBusinessUnitIds($buId),
                sprintf('Failed for %s', $buId)
            );
        }
    }

    public function testAddBusinessUnitShouldSetOwningOrganizationIdEvenIfItIsNull()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu1', null);
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu1'));

        $tree->addBusinessUnit('bu2', 'org');
        $this->assertEquals('org', $tree->getBusinessUnitOrganizationId('bu2'));
    }

    public function testAddBusinessUnitShouldSetOrganizationBusinessUnitIdsOnlyIfOrganizationIsNotNull()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu1', null);
        $this->assertEquals(array(), $tree->getOrganizationBusinessUnitIds('bu1'));

        $tree->addBusinessUnit('bu2', 'org');
        $this->assertEquals(array('bu2'), $tree->getOrganizationBusinessUnitIds('org'));

        $tree->addBusinessUnit('bu3', 'org');
        $this->assertEquals(array('bu2', 'bu3'), $tree->getOrganizationBusinessUnitIds('org'));
    }

    public function testAddBusinessUnitShouldSetBusinessUnitUserIds()
    {
        $tree = new OwnerTree();

        $tree->addUser('user1', 'bu');
        $tree->addUser('user2', 'bu');

        $tree->addBusinessUnit('bu', null);
        $this->assertEquals(array('user1', 'user2'), $tree->getBusinessUnitUserIds('bu'));
    }

    public function testAddBusinessUnitShouldSetUserOwningOrganizationId()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', 'bu');

        $tree->addBusinessUnit('bu', 'org');
        $this->assertEquals('org', $tree->getUserOrganizationId('user'));
    }

    public function testAddBusinessUnitShouldSetUserOwningOrganizationIds()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', 'bu');

        $tree->addBusinessUnit('bu', 'org');
        $this->assertEquals(array('org'), $tree->getUserOrganizationIds('user'));
    }

    public function testAddBusinessUnitShouldNotSetUserOwningOrganizationIdsIfOrganizationIdIsNull()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', 'bu');

        $tree->addBusinessUnit('bu', null);
        $this->assertEquals(array(), $tree->getUserOrganizationIds('user'));
    }

    public function testAddUserShouldSetUserOwningBusinessUnitId()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', 'bu');
        $this->assertEquals('bu', $tree->getUserBusinessUnitId('user'));
    }

    public function testAddUserShouldSetUserOwningBusinessUnitIdEvenIfItIsNull()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', null);
        $this->assertNull($tree->getUserBusinessUnitId('user'));
    }

    public function testAddUserShouldSetUserBusinessUnitIds()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', null);
        $this->assertEquals(array(), $tree->getUserBusinessUnitIds('user'));
    }

    public function testAddUserShouldSetBusinessUnitUserIds()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', null);

        $tree->addUser('user', 'bu');
        $this->assertEquals(array('user'), $tree->getBusinessUnitUserIds('bu'));

        $tree->addUser('user1', 'bu');
        $this->assertEquals(array('user', 'user1'), $tree->getBusinessUnitUserIds('bu'));
    }

    public function testAddUserShouldSetUserOrganizationIds()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', 'org');

        $tree->addUser('user', 'bu');
        $this->assertEquals(array('org'), $tree->getUserOrganizationIds('user'));
    }

    public function testAddUserShouldNotSetUserOrganizationIdsIfOrganizationIdIsNull()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', null);

        $tree->addUser('user', 'bu');
        $this->assertEquals(array(), $tree->getUserOrganizationIds('user'));
    }

    public function testAddUserShouldSetUserOwningOrganizationId()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', 'org');

        $tree->addUser('user', 'bu');
        $this->assertEquals('org', $tree->getUserOrganizationId('user'));
    }

    public function testAddUserShouldSetUserOwningOrganizationIdEvenIfOrganizationIdIsNull()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', null);

        $tree->addUser('user', 'bu');
        $this->assertNull($tree->getUserOrganizationId('user'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testUserBusinessUnitShouldThrowExceptionIfUserDoesNotSet()
    {
        $tree = new OwnerTree();

        $tree->addUserBusinessUnit('user', null);
    }

    public function testUserBusinessUnitShouldNotSetUserBusinessUnitIdsIfBusinessUnitIdIsNull()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', null);

        $tree->addUserBusinessUnit('user', null);
        $this->assertEquals(array(), $tree->getUserBusinessUnitIds('user'));
    }

    public function testUserBusinessUnitShouldSetUserBusinessUnitIds()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', null);

        $tree->addUserBusinessUnit('user', 'bu');
        $this->assertEquals(array('bu'), $tree->getUserBusinessUnitIds('user'));

        $tree->addUserBusinessUnit('user', 'bu1');
        $this->assertEquals(array('bu', 'bu1'), $tree->getUserBusinessUnitIds('user'));
    }

    public function testUserBusinessUnitShouldNotSetUserOrganizationIdsIfOrganizationIdIsNull()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', null);
        $tree->addUser('user', null);

        $tree->addUserBusinessUnit('user', 'bu');
        $this->assertEquals(array(), $tree->getUserOrganizationIds('user'));
    }

    public function testUserBusinessUnitShouldSetUserOrganizationIds()
    {
        $tree = new OwnerTree();

        $tree->addBusinessUnit('bu', 'org');
        $tree->addUser('user', null);

        $tree->addUserBusinessUnit('user', 'bu');
        $this->assertEquals(array('org'), $tree->getUserOrganizationIds('user'));
    }

    public function testAddUserBusinessUnitBelongToDifferentOrganizations()
    {
        $tree = new OwnerTree();

        $tree->addUser('user', null);

        $tree->addBusinessUnit('bu1', null);
        $this->assertNull($tree->getBusinessUnitOrganizationId('bu1'));
        $tree->addBusinessUnit('bu2', 'org2');
        $this->assertEquals('org2', $tree->getBusinessUnitOrganizationId('bu2'));
        $tree->addBusinessUnit('bu3', 'org3');
        $this->assertEquals('org3', $tree->getBusinessUnitOrganizationId('bu3'));

        $tree->addUserBusinessUnit('user', null);
        $this->assertEquals(array(), $tree->getUserBusinessUnitIds('user'));
        $this->assertNull($tree->getUserOrganizationId('user'));
        $this->assertEquals(array(), $tree->getUserOrganizationIds('user'));

        $tree->addUserBusinessUnit('user', 'bu1');
        $this->assertEquals(array('bu1'), $tree->getUserBusinessUnitIds('user'));
        $this->assertNull($tree->getUserOrganizationId('user'));
        $this->assertEquals(array(), $tree->getUserOrganizationIds('user'));

        $tree->addUserBusinessUnit('user', 'bu2');
        $this->assertEquals(array('bu1', 'bu2'), $tree->getUserBusinessUnitIds('user'));
        $this->assertNull($tree->getUserOrganizationId('user'));
        $this->assertEquals(array('org2'), $tree->getUserOrganizationIds('user'));

        $tree->addUserBusinessUnit('user', 'bu3');
        $this->assertEquals(array('bu1', 'bu2', 'bu3'), $tree->getUserBusinessUnitIds('user'));
        $this->assertNull($tree->getUserOrganizationId('user'));
        $this->assertEquals(array('org2', 'org3'), $tree->getUserOrganizationIds('user'));
    }

    public static function addBusinessUnitRelationProvider()
    {
        return array(
            '1: [null]' => array(
                array(
                    array('1', null),
                ),
                array(
                    '1' => array(),
                )
            ),
            '1: [11, 12]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('12', '1'),
                ),
                array(
                    '1' => array('11', '12'),
                    '11' => array(),
                    '12' => array(),
                )
            ),
            '1: [11, 12] reverse' => array(
                array(
                    array('12', '1'),
                    array('11', '1'),
                    array('1', null),
                ),
                array(
                    '1' => array('12', '11'),
                    '11' => array(),
                    '12' => array(),
                )
            ),
            '1: [11: [111], 12]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('111', '11'),
                    array('12', '1'),
                ),
                array(
                    '1' => array('11', '111', '12'),
                    '11' => array('111'),
                    '111' => array(),
                    '12' => array(),
                )
            ),
            '1: [11: [111: [1111, 1112]], 12: [121, 122: [1221]]]' => array(
                array(
                    array('1', null),
                    array('11', '1'),
                    array('111', '11'),
                    array('1111', '111'),
                    array('1112', '111'),
                    array('12', '1'),
                    array('121', '12'),
                    array('122', '12'),
                    array('1221', '122'),
                ),
                array(
                    '1' => array('11', '111', '1111', '1112', '12', '121', '122', '1221'),
                    '11' => array('111', '1111', '1112'),
                    '111' => array('1111', '1112'),
                    '1111' => array(),
                    '1112' => array(),
                    '12' => array('121', '122', '1221'),
                    '121' => array(),
                    '122' => array('1221'),
                )
            ),
        );
    }
}
