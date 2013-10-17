<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;

class EntityMaskBuilderTest extends \PHPUnit_Framework_TestCase
{
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
        $builder = new EntityMaskBuilder();
        $builder->add($maskName);
        $this->assertEquals($mask, $builder->get());

        $builder->remove($maskName);
        $this->assertEquals(EntityMaskBuilder::IDENTITY, $mask = $builder->get());
    }

    public function testGetPattern()
    {
        $builder = new EntityMaskBuilder();
        $this->assertEquals(EntityMaskBuilder::PATTERN_ALL_OFF, $builder->getPattern());
        $this->assertEquals(EntityMaskBuilder::PATTERN_ALL_OFF_BRIEF, $builder->getPattern(true));

        $builder->add('view_basic');
        $expected =
            substr(
                EntityMaskBuilder::PATTERN_ALL_OFF,
                0,
                strlen(EntityMaskBuilder::PATTERN_ALL_OFF) - 1
            )
            . 'V';
        $this->assertEquals($expected, $builder->getPattern());
        $expectedBrief =
            substr(
                EntityMaskBuilder::PATTERN_ALL_OFF_BRIEF,
                0,
                strlen(EntityMaskBuilder::PATTERN_ALL_OFF_BRIEF) - 1
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
        $delim = strpos(EntityMaskBuilder::PATTERN_ALL_OFF, ' ');
        $expected =
            substr(EntityMaskBuilder::PATTERN_ALL_OFF, 0, $delim + 1)
            . '*'
            . substr(
                EntityMaskBuilder::PATTERN_ALL_OFF,
                $delim + 2,
                strlen(EntityMaskBuilder::PATTERN_ALL_OFF) - $delim - 2
            );
        $this->assertEquals($expected, EntityMaskBuilder::getPatternFor((integer) 2147483648));
    }

    public function testReset()
    {
        $builder = new EntityMaskBuilder();
        $this->assertEquals(EntityMaskBuilder::IDENTITY, $builder->get());

        $builder->add('view_basic');
        $this->assertTrue($builder->get() > 0);

        $builder->reset();
        $this->assertEquals(EntityMaskBuilder::IDENTITY, $builder->get());
    }

    /**
     * @dataProvider groupProvider
     */
    public function testGroup($groupMask, $expectedMask)
    {
        $this->assertEquals(
            $expectedMask,
            $groupMask,
            'Actual: ' . EntityMaskBuilder::getPatternFor($groupMask)
        );
    }

    public static function addAndRemoveProvider()
    {
        return array(
            array('VIEW_BASIC', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('CREATE_BASIC', EntityMaskBuilder::MASK_CREATE_BASIC),
            array('EDIT_BASIC', EntityMaskBuilder::MASK_EDIT_BASIC),
            array('DELETE_BASIC', EntityMaskBuilder::MASK_DELETE_BASIC),
            array('ASSIGN_BASIC', EntityMaskBuilder::MASK_ASSIGN_BASIC),
            array('SHARE_BASIC', EntityMaskBuilder::MASK_SHARE_BASIC),
            array('VIEW_LOCAL', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('CREATE_LOCAL', EntityMaskBuilder::MASK_CREATE_LOCAL),
            array('EDIT_LOCAL', EntityMaskBuilder::MASK_EDIT_LOCAL),
            array('DELETE_LOCAL', EntityMaskBuilder::MASK_DELETE_LOCAL),
            array('ASSIGN_LOCAL', EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array('SHARE_LOCAL', EntityMaskBuilder::MASK_SHARE_LOCAL),
            array('VIEW_DEEP', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('CREATE_DEEP', EntityMaskBuilder::MASK_CREATE_DEEP),
            array('EDIT_DEEP', EntityMaskBuilder::MASK_EDIT_DEEP),
            array('DELETE_DEEP', EntityMaskBuilder::MASK_DELETE_DEEP),
            array('ASSIGN_DEEP', EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array('SHARE_DEEP', EntityMaskBuilder::MASK_SHARE_DEEP),
            array('VIEW_GLOBAL', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('CREATE_GLOBAL', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('EDIT_GLOBAL', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('DELETE_GLOBAL', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('ASSIGN_GLOBAL', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('SHARE_GLOBAL', EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array('VIEW_SYSTEM', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('CREATE_SYSTEM', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('EDIT_SYSTEM', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('DELETE_SYSTEM', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('ASSIGN_SYSTEM', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('SHARE_SYSTEM', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array('view_basic', EntityMaskBuilder::MASK_VIEW_BASIC),
            array('create_basic', EntityMaskBuilder::MASK_CREATE_BASIC),
            array('edit_basic', EntityMaskBuilder::MASK_EDIT_BASIC),
            array('delete_basic', EntityMaskBuilder::MASK_DELETE_BASIC),
            array('assign_basic', EntityMaskBuilder::MASK_ASSIGN_BASIC),
            array('share_basic', EntityMaskBuilder::MASK_SHARE_BASIC),
            array('view_local', EntityMaskBuilder::MASK_VIEW_LOCAL),
            array('create_local', EntityMaskBuilder::MASK_CREATE_LOCAL),
            array('edit_local', EntityMaskBuilder::MASK_EDIT_LOCAL),
            array('delete_local', EntityMaskBuilder::MASK_DELETE_LOCAL),
            array('assign_local', EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array('share_local', EntityMaskBuilder::MASK_SHARE_LOCAL),
            array('view_deep', EntityMaskBuilder::MASK_VIEW_DEEP),
            array('create_deep', EntityMaskBuilder::MASK_CREATE_DEEP),
            array('edit_deep', EntityMaskBuilder::MASK_EDIT_DEEP),
            array('delete_deep', EntityMaskBuilder::MASK_DELETE_DEEP),
            array('assign_deep', EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array('share_deep', EntityMaskBuilder::MASK_SHARE_DEEP),
            array('view_global', EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array('create_global', EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array('edit_global', EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array('delete_global', EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array('assign_global', EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array('share_global', EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array('view_system', EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array('create_system', EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array('edit_system', EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array('delete_system', EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array('assign_system', EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array('share_system', EntityMaskBuilder::MASK_SHARE_SYSTEM),
            array(EntityMaskBuilder::MASK_VIEW_BASIC, EntityMaskBuilder::MASK_VIEW_BASIC),
            array(EntityMaskBuilder::MASK_CREATE_BASIC, EntityMaskBuilder::MASK_CREATE_BASIC),
            array(EntityMaskBuilder::MASK_EDIT_BASIC, EntityMaskBuilder::MASK_EDIT_BASIC),
            array(EntityMaskBuilder::MASK_DELETE_BASIC, EntityMaskBuilder::MASK_DELETE_BASIC),
            array(EntityMaskBuilder::MASK_ASSIGN_BASIC, EntityMaskBuilder::MASK_ASSIGN_BASIC),
            array(EntityMaskBuilder::MASK_SHARE_BASIC, EntityMaskBuilder::MASK_SHARE_BASIC),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL, EntityMaskBuilder::MASK_VIEW_LOCAL),
            array(EntityMaskBuilder::MASK_CREATE_LOCAL, EntityMaskBuilder::MASK_CREATE_LOCAL),
            array(EntityMaskBuilder::MASK_EDIT_LOCAL, EntityMaskBuilder::MASK_EDIT_LOCAL),
            array(EntityMaskBuilder::MASK_DELETE_LOCAL, EntityMaskBuilder::MASK_DELETE_LOCAL),
            array(EntityMaskBuilder::MASK_ASSIGN_LOCAL, EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array(EntityMaskBuilder::MASK_SHARE_LOCAL, EntityMaskBuilder::MASK_SHARE_LOCAL),
            array(EntityMaskBuilder::MASK_VIEW_DEEP, EntityMaskBuilder::MASK_VIEW_DEEP),
            array(EntityMaskBuilder::MASK_CREATE_DEEP, EntityMaskBuilder::MASK_CREATE_DEEP),
            array(EntityMaskBuilder::MASK_EDIT_DEEP, EntityMaskBuilder::MASK_EDIT_DEEP),
            array(EntityMaskBuilder::MASK_DELETE_DEEP, EntityMaskBuilder::MASK_DELETE_DEEP),
            array(EntityMaskBuilder::MASK_ASSIGN_DEEP, EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array(EntityMaskBuilder::MASK_SHARE_DEEP, EntityMaskBuilder::MASK_SHARE_DEEP),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL, EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array(EntityMaskBuilder::MASK_CREATE_GLOBAL, EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array(EntityMaskBuilder::MASK_EDIT_GLOBAL, EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array(EntityMaskBuilder::MASK_DELETE_GLOBAL, EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array(EntityMaskBuilder::MASK_ASSIGN_GLOBAL, EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(EntityMaskBuilder::MASK_SHARE_GLOBAL, EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM, EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array(EntityMaskBuilder::MASK_CREATE_SYSTEM, EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array(EntityMaskBuilder::MASK_EDIT_SYSTEM, EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array(EntityMaskBuilder::MASK_DELETE_SYSTEM, EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array(EntityMaskBuilder::MASK_ASSIGN_SYSTEM, EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(EntityMaskBuilder::MASK_SHARE_SYSTEM, EntityMaskBuilder::MASK_SHARE_SYSTEM),
        );
    }

    public static function maskConstantProvider()
    {
        return array(
            array(EntityMaskBuilder::MASK_VIEW_BASIC),
            array(EntityMaskBuilder::MASK_CREATE_BASIC),
            array(EntityMaskBuilder::MASK_EDIT_BASIC),
            array(EntityMaskBuilder::MASK_DELETE_BASIC),
            array(EntityMaskBuilder::MASK_ASSIGN_BASIC),
            array(EntityMaskBuilder::MASK_SHARE_BASIC),
            array(EntityMaskBuilder::MASK_VIEW_LOCAL),
            array(EntityMaskBuilder::MASK_CREATE_LOCAL),
            array(EntityMaskBuilder::MASK_EDIT_LOCAL),
            array(EntityMaskBuilder::MASK_DELETE_LOCAL),
            array(EntityMaskBuilder::MASK_ASSIGN_LOCAL),
            array(EntityMaskBuilder::MASK_SHARE_LOCAL),
            array(EntityMaskBuilder::MASK_VIEW_DEEP),
            array(EntityMaskBuilder::MASK_CREATE_DEEP),
            array(EntityMaskBuilder::MASK_EDIT_DEEP),
            array(EntityMaskBuilder::MASK_DELETE_DEEP),
            array(EntityMaskBuilder::MASK_ASSIGN_DEEP),
            array(EntityMaskBuilder::MASK_SHARE_DEEP),
            array(EntityMaskBuilder::MASK_VIEW_GLOBAL),
            array(EntityMaskBuilder::MASK_CREATE_GLOBAL),
            array(EntityMaskBuilder::MASK_EDIT_GLOBAL),
            array(EntityMaskBuilder::MASK_DELETE_GLOBAL),
            array(EntityMaskBuilder::MASK_ASSIGN_GLOBAL),
            array(EntityMaskBuilder::MASK_SHARE_GLOBAL),
            array(EntityMaskBuilder::MASK_VIEW_SYSTEM),
            array(EntityMaskBuilder::MASK_CREATE_SYSTEM),
            array(EntityMaskBuilder::MASK_EDIT_SYSTEM),
            array(EntityMaskBuilder::MASK_DELETE_SYSTEM),
            array(EntityMaskBuilder::MASK_ASSIGN_SYSTEM),
            array(EntityMaskBuilder::MASK_SHARE_SYSTEM),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function groupProvider()
    {
        return array(
            'GROUP_BASIC' => array(
                EntityMaskBuilder::GROUP_BASIC,
                EntityMaskBuilder::MASK_VIEW_BASIC
                | EntityMaskBuilder::MASK_CREATE_BASIC
                | EntityMaskBuilder::MASK_EDIT_BASIC
                | EntityMaskBuilder::MASK_DELETE_BASIC
                | EntityMaskBuilder::MASK_ASSIGN_BASIC
                | EntityMaskBuilder::MASK_SHARE_BASIC
            ),
            'GROUP_LOCAL' => array(
                EntityMaskBuilder::GROUP_LOCAL,
                EntityMaskBuilder::MASK_VIEW_LOCAL
                | EntityMaskBuilder::MASK_CREATE_LOCAL
                | EntityMaskBuilder::MASK_EDIT_LOCAL
                | EntityMaskBuilder::MASK_DELETE_LOCAL
                | EntityMaskBuilder::MASK_ASSIGN_LOCAL
                | EntityMaskBuilder::MASK_SHARE_LOCAL
            ),
            'GROUP_DEEP' => array(
                EntityMaskBuilder::GROUP_DEEP,
                EntityMaskBuilder::MASK_VIEW_DEEP
                | EntityMaskBuilder::MASK_CREATE_DEEP
                | EntityMaskBuilder::MASK_EDIT_DEEP
                | EntityMaskBuilder::MASK_DELETE_DEEP
                | EntityMaskBuilder::MASK_ASSIGN_DEEP
                | EntityMaskBuilder::MASK_SHARE_DEEP
            ),
            'GROUP_GLOBAL' => array(
                EntityMaskBuilder::GROUP_GLOBAL,
                EntityMaskBuilder::MASK_VIEW_GLOBAL
                | EntityMaskBuilder::MASK_CREATE_GLOBAL
                | EntityMaskBuilder::MASK_EDIT_GLOBAL
                | EntityMaskBuilder::MASK_DELETE_GLOBAL
                | EntityMaskBuilder::MASK_ASSIGN_GLOBAL
                | EntityMaskBuilder::MASK_SHARE_GLOBAL
            ),
            'GROUP_SYSTEM' => array(
                EntityMaskBuilder::GROUP_SYSTEM,
                EntityMaskBuilder::MASK_VIEW_SYSTEM
                | EntityMaskBuilder::MASK_CREATE_SYSTEM
                | EntityMaskBuilder::MASK_EDIT_SYSTEM
                | EntityMaskBuilder::MASK_DELETE_SYSTEM
                | EntityMaskBuilder::MASK_ASSIGN_SYSTEM
                | EntityMaskBuilder::MASK_SHARE_SYSTEM
            ),
            'GROUP_CRUD_SYSTEM' => array(
                EntityMaskBuilder::GROUP_CRUD_SYSTEM,
                EntityMaskBuilder::MASK_VIEW_SYSTEM
                | EntityMaskBuilder::MASK_CREATE_SYSTEM
                | EntityMaskBuilder::MASK_EDIT_SYSTEM
                | EntityMaskBuilder::MASK_DELETE_SYSTEM
            ),
            'GROUP_VIEW' => array(
                EntityMaskBuilder::GROUP_VIEW,
                EntityMaskBuilder::MASK_VIEW_BASIC
                | EntityMaskBuilder::MASK_VIEW_LOCAL
                | EntityMaskBuilder::MASK_VIEW_DEEP
                | EntityMaskBuilder::MASK_VIEW_GLOBAL
                | EntityMaskBuilder::MASK_VIEW_SYSTEM
            ),
            'GROUP_EDIT' => array(
                EntityMaskBuilder::GROUP_EDIT,
                EntityMaskBuilder::MASK_EDIT_BASIC
                | EntityMaskBuilder::MASK_EDIT_LOCAL
                | EntityMaskBuilder::MASK_EDIT_DEEP
                | EntityMaskBuilder::MASK_EDIT_GLOBAL
                | EntityMaskBuilder::MASK_EDIT_SYSTEM
            ),
            'GROUP_CREATE' => array(
                EntityMaskBuilder::GROUP_CREATE,
                EntityMaskBuilder::MASK_CREATE_BASIC
                | EntityMaskBuilder::MASK_CREATE_LOCAL
                | EntityMaskBuilder::MASK_CREATE_DEEP
                | EntityMaskBuilder::MASK_CREATE_GLOBAL
                | EntityMaskBuilder::MASK_CREATE_SYSTEM
            ),
            'GROUP_DELETE' => array(
                EntityMaskBuilder::GROUP_DELETE,
                EntityMaskBuilder::MASK_DELETE_BASIC
                | EntityMaskBuilder::MASK_DELETE_LOCAL
                | EntityMaskBuilder::MASK_DELETE_DEEP
                | EntityMaskBuilder::MASK_DELETE_GLOBAL
                | EntityMaskBuilder::MASK_DELETE_SYSTEM
            ),
            'GROUP_ASSIGN' => array(
                EntityMaskBuilder::GROUP_ASSIGN,
                EntityMaskBuilder::MASK_ASSIGN_BASIC
                | EntityMaskBuilder::MASK_ASSIGN_LOCAL
                | EntityMaskBuilder::MASK_ASSIGN_DEEP
                | EntityMaskBuilder::MASK_ASSIGN_GLOBAL
                | EntityMaskBuilder::MASK_ASSIGN_SYSTEM
            ),
            'GROUP_SHARE' => array(
                EntityMaskBuilder::GROUP_SHARE,
                EntityMaskBuilder::MASK_SHARE_BASIC
                | EntityMaskBuilder::MASK_SHARE_LOCAL
                | EntityMaskBuilder::MASK_SHARE_DEEP
                | EntityMaskBuilder::MASK_SHARE_GLOBAL
                | EntityMaskBuilder::MASK_SHARE_SYSTEM
            ),
            'GROUP_ALL' => array(
                EntityMaskBuilder::GROUP_ALL,
                EntityMaskBuilder::GROUP_BASIC
                | EntityMaskBuilder::GROUP_LOCAL
                | EntityMaskBuilder::GROUP_DEEP
                | EntityMaskBuilder::GROUP_GLOBAL
                | EntityMaskBuilder::GROUP_SYSTEM
            ),
        );
    }
}
