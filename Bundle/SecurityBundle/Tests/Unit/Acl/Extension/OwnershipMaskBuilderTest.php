<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipMaskBuilder;

class OwnershipMaskBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider constructorWithNonIntegerProvider
     */
    public function testConstructorWithNonInteger($invalidMask)
    {
        new OwnershipMaskBuilder($invalidMask);
    }

    public function testConstructorWithoutArguments()
    {
        $builder = new OwnershipMaskBuilder();

        $this->assertEquals(0, $builder->get());
    }

    public function testConstructor()
    {
        $builder = new OwnershipMaskBuilder(123456);

        $this->assertEquals(123456, $builder->get());
    }

    /**
     * @dataProvider maskConstantProvider
     */
    public function testMaskConstant($mask)
    {
        $count = 0;
        $bitmask = decbin($mask);
        for ($i = 0; $i < strlen($bitmask); $i++) {
            if ('1' === $bitmask[$i]) {
                $count++;
            }
        }

        $this->assertEquals(1, $count, sprintf('Each mask must set one and only one bit. Bitmask: %s', $bitmask));
    }

    /**
     * @dataProvider addAndRemoveProvider
     */
    public function testAddAndRemove($maskName, $mask)
    {
        $builder = new OwnershipMaskBuilder();
        $builder->add($maskName);
        $this->assertEquals($mask, $builder->get());

        $builder->remove($maskName);
        $this->assertEquals(0, $mask = $builder->get());
    }

    public function testGetPattern()
    {
        $builder = new OwnershipMaskBuilder();
        $this->assertEquals(OwnershipMaskBuilder::PATTERN_ALL_OFF, $builder->getPattern());
        $this->assertEquals(OwnershipMaskBuilder::PATTERN_ALL_OFF_BRIEF, $builder->getPattern(true));

        $builder->add('view_basic');
        $expected =
            substr(
                OwnershipMaskBuilder::PATTERN_ALL_OFF,
                0,
                strlen(OwnershipMaskBuilder::PATTERN_ALL_OFF) - 1
            )
            . 'V';
        $this->assertEquals($expected, $builder->getPattern());
        $expectedBrief =
            substr(
                OwnershipMaskBuilder::PATTERN_ALL_OFF_BRIEF,
                0,
                strlen(OwnershipMaskBuilder::PATTERN_ALL_OFF_BRIEF) - 1
            )
            . 'V';
        $this->assertEquals($expectedBrief, $builder->getPattern(true));

        $builder->add('view_local');
        $expected = str_replace('. basic:', 'V basic:', $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_deep');
        $expected = str_replace('. local:', 'V local:', $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_global');
        $expected = str_replace('. deep:', 'V deep:', $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_system');
        $expected = str_replace('. global:', 'V global:', $expected);
        $this->assertEquals($expected, $builder->getPattern());
    }

    public function testGetPatternWithUndefinedMask()
    {
        $builder = new OwnershipMaskBuilder((integer)2147483648);
        $delim = strpos(OwnershipMaskBuilder::PATTERN_ALL_OFF, ' ');
        $expected =
            substr(OwnershipMaskBuilder::PATTERN_ALL_OFF, 0, $delim + 1)
            . '*'
            . substr(
                OwnershipMaskBuilder::PATTERN_ALL_OFF,
                $delim + 2,
                strlen(OwnershipMaskBuilder::PATTERN_ALL_OFF) - $delim - 2
            );
        $this->assertEquals($expected, $builder->getPattern());
    }

    public function testReset()
    {
        $builder = new OwnershipMaskBuilder();
        $this->assertEquals(0, $builder->get());

        $builder->add('view_basic');
        $this->assertTrue($builder->get() > 0);

        $builder->reset();
        $this->assertEquals(0, $builder->get());
    }

    public static function constructorWithNonIntegerProvider()
    {
        return array(
            array(234.463),
            array('asdgasdf'),
            array(array()),
            array(new \stdClass()),
        );
    }

    public static function addAndRemoveProvider()
    {
        return array(
            array('VIEW_BASIC', OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array('CREATE_BASIC', OwnershipMaskBuilder::MASK_CREATE_BASIC),
            array('EDIT_BASIC', OwnershipMaskBuilder::MASK_EDIT_BASIC),
            array('DELETE_BASIC', OwnershipMaskBuilder::MASK_DELETE_BASIC),
            array('ASSIGN_BASIC', OwnershipMaskBuilder::MASK_ASSIGN_BASIC),
            array('SHARE_BASIC', OwnershipMaskBuilder::MASK_SHARE_BASIC),
            array('VIEW_LOCAL', OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE_LOCAL', OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT_LOCAL', OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE_LOCAL', OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN_LOCAL', OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE_LOCAL', OwnershipMaskBuilder::MASK_SHARE_LOCAL),
            array('VIEW_DEEP', OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array('CREATE_DEEP', OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array('EDIT_DEEP', OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array('DELETE_DEEP', OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN_DEEP', OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE_DEEP', OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array('VIEW_GLOBAL', OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE_GLOBAL', OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT_GLOBAL', OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE_GLOBAL', OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN_GLOBAL', OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE_GLOBAL', OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array('VIEW_SYSTEM', OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE_SYSTEM', OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT_SYSTEM', OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE_SYSTEM', OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN_SYSTEM', OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE_SYSTEM', OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array('view_basic', OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array('create_basic', OwnershipMaskBuilder::MASK_CREATE_BASIC),
            array('edit_basic', OwnershipMaskBuilder::MASK_EDIT_BASIC),
            array('delete_basic', OwnershipMaskBuilder::MASK_DELETE_BASIC),
            array('assign_basic', OwnershipMaskBuilder::MASK_ASSIGN_BASIC),
            array('share_basic', OwnershipMaskBuilder::MASK_SHARE_BASIC),
            array('view_local', OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array('create_local', OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array('edit_local', OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array('delete_local', OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array('assign_local', OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array('share_local', OwnershipMaskBuilder::MASK_SHARE_LOCAL),
            array('view_deep', OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array('create_deep', OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array('edit_deep', OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array('delete_deep', OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array('assign_deep', OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array('share_deep', OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array('view_global', OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array('create_global', OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array('edit_global', OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array('delete_global', OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array('assign_global', OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('share_global', OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array('view_system', OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array('create_system', OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array('edit_system', OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array('delete_system', OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array('assign_system', OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('share_system', OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC, OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_CREATE_BASIC, OwnershipMaskBuilder::MASK_CREATE_BASIC),
            array(OwnershipMaskBuilder::MASK_EDIT_BASIC, OwnershipMaskBuilder::MASK_EDIT_BASIC),
            array(OwnershipMaskBuilder::MASK_DELETE_BASIC, OwnershipMaskBuilder::MASK_DELETE_BASIC),
            array(OwnershipMaskBuilder::MASK_ASSIGN_BASIC, OwnershipMaskBuilder::MASK_ASSIGN_BASIC),
            array(OwnershipMaskBuilder::MASK_SHARE_BASIC, OwnershipMaskBuilder::MASK_SHARE_BASIC),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL, OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_CREATE_LOCAL, OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array(OwnershipMaskBuilder::MASK_EDIT_LOCAL, OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array(OwnershipMaskBuilder::MASK_DELETE_LOCAL, OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_LOCAL, OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array(OwnershipMaskBuilder::MASK_SHARE_LOCAL, OwnershipMaskBuilder::MASK_SHARE_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP, OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_CREATE_DEEP, OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array(OwnershipMaskBuilder::MASK_EDIT_DEEP, OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array(OwnershipMaskBuilder::MASK_DELETE_DEEP, OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array(OwnershipMaskBuilder::MASK_ASSIGN_DEEP, OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array(OwnershipMaskBuilder::MASK_SHARE_DEEP, OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL, OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL, OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL, OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL, OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL, OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL, OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM, OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM, OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM, OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM, OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM, OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM, OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
        );
    }
    public static function maskConstantProvider()
    {
        return array(
            array(OwnershipMaskBuilder::MASK_VIEW_BASIC),
            array(OwnershipMaskBuilder::MASK_CREATE_BASIC),
            array(OwnershipMaskBuilder::MASK_EDIT_BASIC),
            array(OwnershipMaskBuilder::MASK_DELETE_BASIC),
            array(OwnershipMaskBuilder::MASK_ASSIGN_BASIC),
            array(OwnershipMaskBuilder::MASK_SHARE_BASIC),
            array(OwnershipMaskBuilder::MASK_VIEW_LOCAL),
            array(OwnershipMaskBuilder::MASK_CREATE_LOCAL),
            array(OwnershipMaskBuilder::MASK_EDIT_LOCAL),
            array(OwnershipMaskBuilder::MASK_DELETE_LOCAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_LOCAL),
            array(OwnershipMaskBuilder::MASK_SHARE_LOCAL),
            array(OwnershipMaskBuilder::MASK_VIEW_DEEP),
            array(OwnershipMaskBuilder::MASK_CREATE_DEEP),
            array(OwnershipMaskBuilder::MASK_EDIT_DEEP),
            array(OwnershipMaskBuilder::MASK_DELETE_DEEP),
            array(OwnershipMaskBuilder::MASK_ASSIGN_DEEP),
            array(OwnershipMaskBuilder::MASK_SHARE_DEEP),
            array(OwnershipMaskBuilder::MASK_VIEW_GLOBAL),
            array(OwnershipMaskBuilder::MASK_CREATE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_EDIT_GLOBAL),
            array(OwnershipMaskBuilder::MASK_DELETE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(OwnershipMaskBuilder::MASK_SHARE_GLOBAL),
            array(OwnershipMaskBuilder::MASK_VIEW_SYSTEM),
            array(OwnershipMaskBuilder::MASK_CREATE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_EDIT_SYSTEM),
            array(OwnershipMaskBuilder::MASK_DELETE_SYSTEM),
            array(OwnershipMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(OwnershipMaskBuilder::MASK_SHARE_SYSTEM),
        );
    }
}
