<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\OwnershipConditionDataBuilder;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProviderStub;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;

class OwnershipFilterBuilderTest extends \PHPUnit_Framework_TestCase
{
    const BUSINESS_UNIT = 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\BusinessUnit';
    const ORGANIZATION = 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\Organization';
    const USER = 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\User';
    const TEST_ENTITY = 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity';

    /**
     * @var OwnershipConditionDataBuilder
     */
    private $builder;

    /** @var OwnershipMetadataProviderStub */
    private $metadataProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aclVoter;

    /** @var OwnerTree */
    private $tree;

    protected function setUp()
    {
        $this->tree = new OwnerTree();

        $treeProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $treeProvider->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($this->tree));

        $entityMetadataProvider =
            $this->getMockBuilder('Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider')
                ->disableOriginalConstructor()
                ->getMock();
        $entityMetadataProvider->expects($this->any())
            ->method('isProtectedEntity')
            ->will($this->returnValue(true));

        $this->metadataProvider = new OwnershipMetadataProviderStub($this);
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getOrganizationClass(),
            new OwnershipMetadata()
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getBusinessUnitClass(),
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );
        $this->metadataProvider->setMetadata(
            $this->metadataProvider->getUserClass(),
            new OwnershipMetadata('BUSINESS_UNIT', 'owner', 'owner_id')
        );

        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContextLink =
            $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
                ->disableOriginalConstructor()
                ->getMock();
        $securityContextLink->expects($this->any())->method('getService')
            ->will($this->returnValue($this->securityContext));
        $this->aclVoter = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new OwnershipConditionDataBuilder(
            $securityContextLink,
            new ObjectIdAccessor(),
            $entityMetadataProvider,
            $this->metadataProvider,
            $treeProvider,
            $this->aclVoter
        );
    }

    private function buildTestTree()
    {
        /**
         * org1  org2     org3         org4
         *                |            |
         *  bu1   bu2     +-bu3        +-bu4
         *        |       | |            |
         *        |       | +-bu31       |
         *        |       | | |          |
         *        |       | | +-user31   |
         *        |       | |            |
         *  user1 +-user2 | +-user3      +-user4
         *                |                |
         *                +-bu3a           +-bu3
         *                  |              +-bu4
         *                  +-bu3a1          |
         *                                   +-bu41
         *                                     |
         *                                     +-bu411
         *                                       |
         *                                       +-user411
         */
        $this->tree->addBusinessUnit('bu1', null);
        $this->tree->addBusinessUnit('bu2', null);
        $this->tree->addBusinessUnit('bu3', 'org3');
        $this->tree->addBusinessUnit('bu31', 'org3');
        $this->tree->addBusinessUnit('bu3a', 'org3');
        $this->tree->addBusinessUnit('bu3a1', 'org3');
        $this->tree->addBusinessUnit('bu4', 'org4');
        $this->tree->addBusinessUnit('bu41', 'org4');
        $this->tree->addBusinessUnit('bu411', 'org4');

        $this->tree->addBusinessUnitRelation('bu1', null);
        $this->tree->addBusinessUnitRelation('bu2', null);
        $this->tree->addBusinessUnitRelation('bu3', null);
        $this->tree->addBusinessUnitRelation('bu31', 'bu3');
        $this->tree->addBusinessUnitRelation('bu3a', null);
        $this->tree->addBusinessUnitRelation('bu3a1', 'bu3a');
        $this->tree->addBusinessUnitRelation('bu4', null);
        $this->tree->addBusinessUnitRelation('bu41', 'bu4');
        $this->tree->addBusinessUnitRelation('bu411', 'bu41');

        $this->tree->addUser('user1', null);
        $this->tree->addUser('user2', 'bu2');
        $this->tree->addUser('user3', 'bu3');
        $this->tree->addUser('user31', 'bu31');
        $this->tree->addUser('user4', 'bu4');
        $this->tree->addUser('user41', 'bu41');
        $this->tree->addUser('user411', 'bu411');

        $this->tree->addUserBusinessUnit('user4', 'bu3');
        $this->tree->addUserBusinessUnit('user4', 'bu4');
    }

    /**
     * @dataProvider buildFilterConstraintProvider
     */
    public function testGetAclConditionData(
        $userId,
        $isGranted,
        $accessLevel,
        $ownerType,
        $targetEntityClassName,
        $targetTableAlias,
        $expectedConstraint
    ) {
        $this->buildTestTree();

        if ($ownerType !== null) {
            $this->metadataProvider->setMetadata(
                self::TEST_ENTITY,
                new OwnershipMetadata($ownerType, 'owner', 'owner_id')
            );
        }

        /** @var OneShotIsGrantedObserver $aclObserver */
        $aclObserver = null;
        $this->aclVoter->expects($this->any())
            ->method('addOneShotIsGrantedObserver')
            ->will(
                $this->returnCallback(
                    function ($observer) use (&$aclObserver, &$accessLevel) {
                        $aclObserver = $observer;
                        /** @var OneShotIsGrantedObserver $aclObserver */
                        $aclObserver->setAccessLevel($accessLevel);
                    }
                )
            );

        $user = new User($userId);
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->securityContext->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo('VIEW'), $this->equalTo('entity:' . $targetEntityClassName))
            ->will($this->returnValue($isGranted));
        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($userId ? $token : null));

        $result = $this->builder->getAclConditionData($targetEntityClassName);

        $this->assertEquals(
            $expectedConstraint,
            $result
        );
    }

    public function testGetUserIdWithNonLoginUser()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue('anon'));
        $this->securityContext->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));
        $this->assertNull($this->builder->getUserId());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function buildFilterConstraintProvider()
    {
        return array(
            array('', false, AccessLevel::NONE_LEVEL, null, self::TEST_ENTITY, '', []),
            array('', false, AccessLevel::NONE_LEVEL, null, self::TEST_ENTITY, 't', []),
            array('', true, AccessLevel::NONE_LEVEL, null, '\stdClass', '', []),
            array('user4', true, AccessLevel::SYSTEM_LEVEL, null, self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::SYSTEM_LEVEL, 'ORGANIZATION', self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::SYSTEM_LEVEL, 'BUSINESS_UNIT', self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::SYSTEM_LEVEL, 'USER', self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::GLOBAL_LEVEL, null, self::TEST_ENTITY, '', []),
            array(
                'user4',
                true,
                AccessLevel::GLOBAL_LEVEL,
                'ORGANIZATION',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('org3', 'org4')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::GLOBAL_LEVEL,
                'BUSINESS_UNIT',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('bu3', 'bu31', 'bu3a', 'bu3a1', 'bu4', 'bu41', 'bu411')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::GLOBAL_LEVEL,
                'USER',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('user3', 'user31', 'user4', 'user41', 'user411')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::GLOBAL_LEVEL,
                null,
                self::ORGANIZATION,
                '',
                array(
                    'id',
                    array('org3', 'org4')
                )
            ),
            array('user4', true, AccessLevel::DEEP_LEVEL, null, self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::DEEP_LEVEL, 'ORGANIZATION', self::TEST_ENTITY, '', null),
            array(
                'user4',
                true,
                AccessLevel::DEEP_LEVEL,
                'BUSINESS_UNIT',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('bu3', 'bu4', 'bu31', 'bu41', 'bu411')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::DEEP_LEVEL,
                'USER',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('user3', 'user4', 'user31', 'user41', 'user411')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::DEEP_LEVEL,
                null,
                self::BUSINESS_UNIT,
                '',
                array(
                    'id',
                    array('bu3', 'bu4', 'bu31', 'bu41', 'bu411')
                )
            ),
            array('user4', true, AccessLevel::LOCAL_LEVEL, null, self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::LOCAL_LEVEL, 'ORGANIZATION', self::TEST_ENTITY, '', null),
            array(
                'user4',
                true,
                AccessLevel::LOCAL_LEVEL,
                'BUSINESS_UNIT',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('bu3', 'bu4')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::LOCAL_LEVEL,
                'USER',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    array('user3', 'user4')
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::LOCAL_LEVEL,
                null,
                self::BUSINESS_UNIT,
                '',
                array(
                    'id',
                    array('bu3', 'bu4')
                )
            ),
            array('user4', true, AccessLevel::BASIC_LEVEL, null, self::TEST_ENTITY, '', []),
            array('user4', true, AccessLevel::BASIC_LEVEL, 'ORGANIZATION', self::TEST_ENTITY, '', null),
            array('user4', true, AccessLevel::BASIC_LEVEL, 'BUSINESS_UNIT', self::TEST_ENTITY, '', null),
            array(
                'user4',
                true,
                AccessLevel::BASIC_LEVEL,
                'USER',
                self::TEST_ENTITY,
                '',
                array(
                    'owner',
                    'user4'
                )
            ),
            array(
                'user4',
                true,
                AccessLevel::BASIC_LEVEL,
                null,
                self::USER,
                '',
                array(
                    'id',
                    'user4'
                )
            )
        );
    }
}
