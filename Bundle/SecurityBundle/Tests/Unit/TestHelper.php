<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectClassAccessor;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Extension\AspectAclExtension;
use Oro\Bundle\SecurityBundle\Owner\ObjectOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\OwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\OwnershipMetadataProvider;

class TestHelper
{
    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $em
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $ownerTree
     * @return AclExtensionSelector
     */
    public static function createAclExtensionSelector(
        \PHPUnit_Framework_MockObject_MockObject $em,
        OwnershipMetadataProvider $metadataProvider = null,
        OwnerTree $ownerTree = null
    ) {
        if ($metadataProvider === null) {
            $metadataProvider = new OwnershipMetadataProvider();
        }
        if ($ownerTree === null) {
            $ownerTree = new OwnerTree();
        }

        $classAccessor = new ObjectClassAccessor();
        $idAccessor = new ObjectIdAccessor();

        $decisionMaker = new OwnershipDecisionMaker(
            $ownerTree,
            $classAccessor,
            $idAccessor,
            new ObjectOwnerAccessor($classAccessor, $metadataProvider),
            $metadataProvider
        );

        $selector = new AclExtensionSelector();
        $selector->addAclExtension(
            new ActionAclExtension()
        );
        $selector->addAclExtension(
            new AspectAclExtension()
        );
        $selector->addAclExtension(
            new OwnershipAclExtension(
                $classAccessor,
                $idAccessor,
                new EntityClassResolver($em),
                $metadataProvider,
                $decisionMaker
            )
        );

        return $selector;
    }
}
