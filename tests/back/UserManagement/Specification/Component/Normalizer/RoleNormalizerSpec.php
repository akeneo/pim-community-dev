<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Normalizer\RoleNormalizer;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadata;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoleNormalizerSpec extends ObjectBehavior
{
    function let(AclManager $aclManager, NormalizerInterface $aclPrivilegeNormalizer, AclExtensionInterface $extension)
    {
        $aclManager->getAllExtensions()->willReturn([$extension]);
        $extension->getExtensionKey()->willReturn('action');
        $extension->getClasses()->willReturn([
            new ActionMetadata('name1'),
            new ActionMetadata('name2'),
        ]);

        $this->beConstructedWith($aclManager, $aclPrivilegeNormalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(RoleNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_role(
        AclManager $aclManager,
        NormalizerInterface $aclPrivilegeNormalizer,
        AclPrivilegeRepository $aclPrivilegeRepository
    ) {
        $role = new Role('Administrator');
        $format = 'standard';


        $sid = new RoleSecurityIdentity($role);
        $aclManager->getSid($role)->willReturn($sid);
        $aclManager->getPrivilegeRepository()->willReturn($aclPrivilegeRepository);
        $aclPrivileges = $this->buildAclPrivileges();
        $aclPrivilegeRepository->getPrivileges($sid)->willReturn($aclPrivileges);

        $aclPrivilegeNormalizer->normalize($aclPrivileges[0], 'array', [])->willReturn([
            'id' => 'action:name1',
            'name' => 'name1',
            'group' => 'group1',
            'type' => 'action',
            'permissions' => [
                'EXECUTE' => ['name' => 'EXECUTE', 'access_level' => 1],
            ],
        ]);
        $aclPrivilegeNormalizer->normalize($aclPrivileges[1], 'array', [])->willReturn([
            'id' => 'action:name2',
            'name' => 'name2',
            'group' => 'group2',
            'type' => 'action',
            'permissions' => [
                'VIEW' => ['name' => 'VIEW', 'access_level' => 1],
                'CREATE' => ['name' => 'CREATE', 'access_level' => 0],
            ],
        ]);
        $aclPrivilegeNormalizer->normalize($aclPrivileges[2], 'array', [])->shouldNotBeCalled();


        $this->supportsNormalization($role, $format)->shouldBe(true);
        $this->normalize($role, 'array')->shouldBe(['label' => 'Administrator', 'permissions' => [
            [
                'id' => 'action:name1',
                'name' => 'name1',
                'group' => 'group1',
                'type' => 'action',
                'permissions' => [
                    'EXECUTE' => ['name' => 'EXECUTE', 'access_level' => 1],
                ],
            ],
            [
                'id' => 'action:name2',
                'name' => 'name2',
                'group' => 'group2',
                'type' => 'action',
                'permissions' => [
                    'VIEW' => ['name' => 'VIEW', 'access_level' => 1],
                    'CREATE' => ['name' => 'CREATE', 'access_level' => 0],
                ],
            ],
        ]]);
    }

    function it_cannot_normalize_a_non_role_class_instance()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('normalize', [new \StdClass()]);
    }

    function it_cannot_normalize_a_role_with_an_unknown_format()
    {
        $role = new Role('Administrator');
        $format = 'unknown';

        $this->supportsNormalization($role, $format)->shouldBe(false);
    }

    function buildAclPrivileges(): array
    {
        $aclPermission = new AclPermission('EXECUTE', 1);
        $aclPrivilege1 = new AclPrivilege();
        $aclPrivilege1->setIdentity(new AclPrivilegeIdentity('action:name1', 'name1'));
        $aclPrivilege1->setGroup('group1');
        $aclPrivilege1->setExtensionKey('action');
        $aclPrivilege1->addPermission($aclPermission);

        $aclPermission1 = new AclPermission('VIEW', 1);
        $aclPermission2 = new AclPermission('CREATE', 0);
        $aclPrivilege2 = new AclPrivilege();
        $aclPrivilege2->setIdentity(new AclPrivilegeIdentity('action:name2', 'name2'));
        $aclPrivilege2->setGroup('group2');
        $aclPrivilege2->setExtensionKey('action');
        $aclPrivilege2->addPermission($aclPermission1);
        $aclPrivilege2->addPermission($aclPermission2);

        $aclPermission = new AclPermission('EXECUTE', 1);
        $aclPrivilege3 = new AclPrivilege();
        $aclPrivilege3->setIdentity(new AclPrivilegeIdentity('other:name1', 'name1'));
        $aclPrivilege3->setGroup('group1');
        $aclPrivilege3->setExtensionKey('other');
        $aclPrivilege3->addPermission($aclPermission);

        return [$aclPrivilege1, $aclPrivilege2, $aclPrivilege3];
    }
}
