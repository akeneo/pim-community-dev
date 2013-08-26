<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\OwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;

class TestHelper
{
    public static function get(\PHPUnit_Framework_TestCase $testCase)
    {
        return new TestHelper($testCase);
    }

    /**
     * @var (\PHPUnit_Framework_TestCase
     */
    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $ownerTree
     * @return AclExtensionSelector
     */
    public function createAclExtensionSelector(
        OwnershipMetadataProvider $metadataProvider = null,
        OwnerTree $ownerTree = null
    ) {
        $classAccessor = new ObjectClassAccessor();
        $idAccessor = new ObjectIdAccessor();
        $selector = new AclExtensionSelector($classAccessor, $idAccessor);
        $selector->addAclExtension(
            new ActionAclExtension()
        );
        $selector->addAclExtension(
            $this->createOwnershipAclExtension($metadataProvider, $ownerTree, $classAccessor, $idAccessor)
        );

        return $selector;
    }

    /**
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $ownerTree
     * @param ObjectClassAccessor $classAccessor
     * @param ObjectIdAccessor $idAccessor
     * @return OwnershipAclExtension
     */
    public function createOwnershipAclExtension(
        OwnershipMetadataProvider $metadataProvider = null,
        OwnerTree $ownerTree = null,
        ObjectClassAccessor $classAccessor = null,
        ObjectIdAccessor $idAccessor = null
    ) {
        if ($classAccessor === null) {
            $classAccessor = new ObjectClassAccessor();
        }
        if ($idAccessor === null) {
            $idAccessor = new ObjectIdAccessor();
        }
        if ($metadataProvider === null) {
            $metadataProvider = new OwnershipMetadataProvider();
        }
        if ($ownerTree === null) {
            $ownerTree = new OwnerTree();
        }

        $decisionMaker = new OwnershipDecisionMaker(
            $ownerTree,
            $classAccessor,
            $idAccessor,
            new ObjectOwnerAccessor($classAccessor, $metadataProvider),
            $metadataProvider
        );

        $config = $this->testCase->getMockBuilder('\Doctrine\ORM\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->testCase->any())
            ->method('getEntityNamespaces')
            ->will(
                $this->testCase->returnValue(
                    array(
                        'Test' => 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity'
                    )
                )
            );

        $em = $this->testCase->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->testCase->any())
            ->method('getConfiguration')
            ->will($this->testCase->returnValue($config));

        $doctrine = $this->testCase->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->testCase->any())
            ->method('getManagers')
            ->will($this->testCase->returnValue(array('default' => $em)));
        $doctrine->expects($this->testCase->any())
            ->method('getManager')
            ->with($this->testCase->equalTo('default'))
            ->will($this->testCase->returnValue($em));
        $doctrine->expects($this->testCase->any())
            ->method('getAliasNamespace')
            ->will(
                $this->testCase->returnValueMap(
                    array(
                        array('Test', 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity'),
                    )
                )
            );

        return new OwnershipAclExtension(
            $classAccessor,
            $idAccessor,
            new EntityClassResolver($doctrine),
            $metadataProvider,
            $decisionMaker
        );
    }
}
