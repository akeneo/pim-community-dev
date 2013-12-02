<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Owner;

use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class OwnerTreeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnerTreeProvider
     */
    protected $treeProvider;

    protected $em;
    protected $cache;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockForAbstractClass('Doctrine\Common\Cache\CacheProvider');

        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->cache->expects($this->any())
            ->method('save');

        $this->treeProvider = new OwnerTreeProvider($this->em, $this->cache);
    }

    public function testGetTree()
    {
        $userRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $buRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('Oro\Bundle\UserBundle\Entity\User'))
            ->will($this->returnValue($userRepo));
        $this->em->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit'))
            ->will($this->returnValue($buRepo));

        list($users, $bUnits) = $this->getTestData();

        $userRepo->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($users));

        $buRepo->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($bUnits));

        $this->treeProvider->warmUpCache();
        $tree = $this->treeProvider->getTree();
        $this->assertEquals(1, $tree->getBusinessUnitOrganizationId(1));
        $this->assertEquals([1], $tree->getUserOrganizationIds(1));
    }

    protected function setId($object, $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    protected function getTestData()
    {
        $organization = new Organization();
        $this->setId($organization, 1);

        $mainBu = new BusinessUnit();
        $this->setId($mainBu, 1);
        $mainBu->setOrganization($organization);

        $bu2 = new BusinessUnit();
        $this->setId($bu2, 2);
        $bu2->setOrganization($organization);

        $childBu = new BusinessUnit();
        $this->setId($childBu, 3);
        $childBu->setOrganization($organization);
        $childBu->setOwner($mainBu);

        $user1 = new User();
        $this->setId($user1, 1);
        $user1->setOwner($mainBu);
        $user1->addBusinessUnit($mainBu);

        $user2 = new User();
        $this->setId($user2, 2);
        $user2->setOwner($bu2);
        $user2->addBusinessUnit($bu2);

        $user3 = new User();
        $this->setId($user3, 3);
        $user3->setOwner($childBu);
        $user3->addBusinessUnit($childBu);

        return [
            [$user1, $user2, $user3],
            [$mainBu, $bu2, $childBu]
        ];
    }
}
