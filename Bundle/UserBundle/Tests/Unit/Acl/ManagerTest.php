<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use Oro\Bundle\UserBundle\Acl\Manager;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Annotation\Acl as AclAnnotation;
use Symfony\Component\Translation\MessageCatalogue;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\Manager
     */
    private $manager;

    private $user;

    private $repository;

    private $om;

    private $cache;

    private $testRole;

    private $aclObject;

    private $testUser;

    private $securityContext;

    private $reader;

    private $configApiReader;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->securityContext = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Core\SecurityContextInterface'
        );

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array(
                'find',
                'findAll',
                'findBy',
                'findOneBy',
                'getClassName',
                'getAllowedAclResourcesForRoles',
                'getFullNodeWithRoles',
                'getAclRolesWithoutTree',
                'getRoleAclTree',
                'getAclListWithRoles'
            )
        );

        $this->repository->expects($this->any())
            ->method('getAllowedAclResourcesForRoles')
            ->will($this->returnValue(array('test')));

        $this->user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        $this->om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->reader = $this->getMock(
            'Oro\Bundle\UserBundle\Acl\ResourceReader\Reader',
            array(),
            array(),
            '',
            false
        );

        $this->configApiReader = $this->getMock(
            'Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader',
            array(),
            array(),
            '',
            false
        );

        $sqlExecMock = $this->getMock('Doctrine\ORM\Query\Exec\AbstractSqlExecutor', array('execute'));
        $sqlExecMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(10));
        $parserResultMock = $this->getMock('Doctrine\ORM\Query\ParserResult');
        $parserResultMock->expects($this->any())
            ->method('getSqlExecutor')
            ->will($this->returnValue($sqlExecMock));
        $this->cache = $this->getMock(
            'Doctrine\Common\Cache\CacheProvider',
            array('doFetch', 'doContains', 'doSave', 'doDelete', 'doFlush', 'doGetStats', 'fetch', 'save')
        );

        $this->manager = new Manager(
            $this->om,
            $this->reader,
            $this->cache,
            $this->securityContext,
            $this->configApiReader
        );

        $this->testRole = new Role('ROLE_TEST_ROLE');

        $this->testUser = new User();
        $this->testUser->addRole($this->testRole);

        $this->aclObject = new Acl();
        $this->aclObject->setDescription('test_acl')
            ->setId('test_acl')
            ->setName('test_acl')
            ->addAccessRole($this->testRole);
    }

    public function testIsResourceGranted()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->aclObject));

        $this->repository->expects($this->once())
            ->method('getFullNodeWithRoles')
            ->with($this->equalTo($this->aclObject))
            ->will($this->returnValue(array($this->aclObject)));

        $this->assertEquals(true, $this->manager->isResourceGranted('test_acl', $this->testUser));
    }

    public function testIsClassMethodGranted()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(
                $this->equalTo(
                    array(
                        'class'  => 'test_class',
                        'method' => 'test_method'
                    )
                )
            )
            ->will($this->returnValue($this->aclObject));

        $this->repository->expects($this->once())
            ->method('getFullNodeWithRoles')
            ->with($this->equalTo($this->aclObject))
            ->will($this->returnValue(array($this->aclObject)));

        $this->assertEquals(true, $this->manager->isClassMethodGranted('test_class', 'test_method', $this->testUser));
    }

    public function testIsClassMethodGrantedWoAcl()
    {
        $this->repository->expects($this->any())
            ->method('getAclRolesWithoutTree')
            ->will($this->returnValue(array($this->aclObject)));

        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $this->assertEquals(false, $this->manager->isClassMethodGranted('test_class', 'test_method', $this->testUser));
    }

    public function testGetAclForUser()
    {
        $this->cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $result = $this->manager->getAclForUser($this->user);
        $this->assertEquals(array('test'), $result);

        $result = $this->manager->getAclForUser($this->user, true);
        $this->assertEquals(array('test'), $result);
    }

    public function testGetAclRoles()
    {

        $testAclName = 'test_acl';

        $this->cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->aclObject));

        $this->repository->expects($this->once())
            ->method('getFullNodeWithRoles')
            ->with($this->equalTo($this->aclObject))
            ->will($this->returnValue(array($this->aclObject)));

        $this->assertEquals(
            array('ROLE_TEST_ROLE'),
            $this->manager->getAclRoles($testAclName)
        );
    }

    public function testGetRoleAclTree()
    {
        $this->repository->expects($this->once())
            ->method('getRoleAclTree')
            ->with($this->equalTo($this->testRole))
            ->will($this->returnValue(array()));

        $this->manager->getRoleAclTree($this->testRole);
    }

    public function testGetRoleAcl()
    {
        $this->repository->expects($this->once())
            ->method('getAclListWithRoles')
            ->with($this->equalTo($this->testRole))
            ->will($this->returnValue(array()));

        $this->manager->getRoleAcl($this->testRole);
    }

    public function testSynchronizeAclResources()
    {
        $resources = array();
        for ($i = 0; $i < 10; $i++) {
            $acl = new Acl();
            $acl->setId('test_acl_' . $i);
            $acl->setName('test_acl_' . $i);
            $resources[] = $acl;
        }

        $annotations = array();
        for ($i = 5; $i < 15; $i++) {
            $annotation = new AclAnnotation(
                array(
                    'id'          => 'test_acl_' . $i,
                    'name'        => 'test_acl_' . $i,
                    'description' => 'test',
                    'parent'      => 'test_acl_' . ($i - 1),
                )
            );
            $annotations['test_acl_' . $i] = $annotation;
        }
        for ($i = 20; $i < 22; $i++) {
            $annotation = new AclAnnotation(
                array(
                    'id'          => 'test_acl_' . $i,
                    'name'        => 'test_acl_' . $i,
                    'description' => 'test'
                )
            );
            $annotations['test_acl_' . $i] = $annotation;
        }
        $annotations['child'] = new AclAnnotation(
            array(
                'id'          => 'child',
                'name'        => 'child',
                'description' => 'child',
                'parent'      => 'parent'
            )
        );
        $annotations['parent'] = new AclAnnotation(
            array(
                'id'          => 'parent',
                'name'        => 'parent',
                'description' => 'parent'
            )
        );

        $this->reader->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue($annotations));

        $this->configApiReader->expects($this->once())
            ->method('getConfigResources')
            ->will($this->returnValue(array()));

        $this->repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($resources));

        $this->manager->synchronizeAclResources();
    }

    public function testSaveRoleAcl()
    {
        for ($i = 0; $i < 10; $i++) {
            $acl = new Acl();
            $acl->setId('test_acl_' . $i);
            $acl->setName('test_acl_' . $i);
            $this->testRole->addAclResource($acl);
            $aclResourses[] = $acl;
        }

        $gfParentAcl = new Acl();
        $gfParentAcl->setId('grand_pa_acl');
        $parentAcl = new Acl();
        $parentAcl->setId('parent');
        $parentAcl->setParent($gfParentAcl);
        $aclWithParent = $aclResourses[8];
        $aclWithParent->setParent($parentAcl);
        $aclResourses[8] = $aclWithParent;

        $this->repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($aclWithParent));

        $this->manager->saveRoleAcl($this->testRole, $aclResourses);
    }

    public function testParseAclTokens()
    {
        $this->reader->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array(
                new AclAnnotation(
                    array(
                         'id'          => 'test_acl_1' ,
                         'name'        => 'name_test_acl_1' ,
                         'description' => 'description_test 1',
                    )
                )
            )));

        $this->configApiReader->expects($this->once())
            ->method('getConfigResources')
            ->will($this->returnValue(array()));

        $messageCatalogue = new MessageCatalogue('en');
        $this->manager->parseAclTokens('', $messageCatalogue, null);

        $messages = $messageCatalogue->all();

        $this->assertEquals('name_test_acl_1', $messages['messages']['name_test_acl_1']);
        $this->assertEquals('description_test 1', $messages['messages']['description_test 1']);
    }
}
