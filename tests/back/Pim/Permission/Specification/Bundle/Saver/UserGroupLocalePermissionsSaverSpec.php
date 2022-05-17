<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllActiveLocalesCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocaleReferenceFromCode;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetActiveLocalesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;

class UserGroupLocalePermissionsSaverSpec extends ObjectBehavior
{
    private const DEFAULT_PERMISSION_EDIT = 'locale_edit';
    private const DEFAULT_PERMISSION_VIEW = 'locale_view';

    function let(
        LocaleAccessManager $localeAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetAllActiveLocalesCodes $getAllActiveLocalesCodes,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel,
        GetActiveLocaleReferenceFromCode $getActiveLocaleReferenceFromCode,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB,
        LocaleInterface $localeC
    ) {
        $group->getId()->willReturn(42);
        $groupRepository->findOneByIdentifier('Redactor')->willReturn($group);

        $localeA->getId()->willReturn(1);
        $localeA->getCode()->willReturn('locale_a');
        $localeB->getId()->willReturn(2);
        $localeB->getCode()->willReturn('locale_b');
        $localeC->getId()->willReturn(3);
        $localeC->getCode()->willReturn('locale_c');

        $getAllActiveLocalesCodes->execute()->willReturn(['locale_a', 'locale_b', 'locale_c']);

        $getActiveLocaleReferenceFromCode->execute('locale_a')->willReturn($localeA);
        $getActiveLocaleReferenceFromCode->execute('locale_b')->willReturn($localeB);
        $getActiveLocaleReferenceFromCode->execute('locale_c')->willReturn($localeC);

        $getActiveLocalesAccessesWithHighestLevel->execute(42)->willReturn([]);

        $this->beConstructedWith(
            $localeAccessManager,
            $groupRepository,
            $groupSaver,
            $getAllActiveLocalesCodes,
            $getActiveLocalesAccessesWithHighestLevel,
            $getActiveLocaleReferenceFromCode,
        );
    }

    public function it_throws_logic_exception_when_group_is_not_found(GroupRepository $groupRepository)
    {
        $groupRepository->findOneByIdentifier('Redactor')->willReturn(null);
        $this->shouldThrow(\LogicException::class)->during('save', ['Redactor', []]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingLocalesWhenTheAllByDefaultOptionIsEnabled(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB,
        LocaleInterface $localeC
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_VIEW, true)->shouldBeCalled();
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_EDIT, true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeB, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeC, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_CorrectsGrantedPermissionsOnExistingLocalesWhenTheDefaultOptionIsReducedToViewOnly(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB,
        LocaleInterface $localeC
    ) {
        $group->getDefaultPermissions()->willReturn([
            self::DEFAULT_PERMISSION_VIEW => true,
            self::DEFAULT_PERMISSION_EDIT => true,
        ]);
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_VIEW, true)->shouldBeCalled();
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_EDIT, false)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $localeAccessManager->grantAccess($localeA, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeC, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a"]}}
     */
    public function it_GrantPermissionsOnExistingLocalesWhenIdentifiersAreSelected(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $groupSaver->save($group)->shouldNotBeCalled();

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
            'view' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingLocalesWhenIdentifiersAndTheDefaultOptionAreMixed(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB,
        LocaleInterface $localeC
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_VIEW, true)->shouldBeCalled();
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_EDIT, false)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeC, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a", "locale_b"]}}
     */
    public function it_GrantPermissionsOnLocalesWhenIdentifiersAreOnDifferentLevels(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $groupSaver->save($group)->shouldNotBeCalled();

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $localeAccessManager->grantAccess($localeB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
            'view' => [
                'all' => false,
                'identifiers' => ['locale_a', 'locale_b'],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a"]}}
     * TO {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a"]}}
     */
    public function it_doesNothingWhenIdentifiersWereAlreadySelected(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getActiveLocalesAccessesWithHighestLevel->execute(42)->willReturn(['locale_a' => Attributes::EDIT_ITEMS]);

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldNotBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
            'view' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a"]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":[]}}
     */
    public function it_removeAccessWhenIdentifiersAreRemoved(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getActiveLocalesAccessesWithHighestLevel->execute(42)->willReturn(['locale_a' => Attributes::EDIT_ITEMS]);

        $localeAccessManager->revokeGroupAccess($localeA, $group)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a"]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":["locale_a"]}}
     */
    public function it_updatesPermissionsWhenIdentifiersAreRemoved(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getActiveLocalesAccessesWithHighestLevel->execute(42)->willReturn(['locale_a' => Attributes::EDIT_ITEMS]);

        $localeAccessManager->grantAccess($localeA, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["locale_a", "locale_b"]},"view":{"all":true,"identifiers":[]}}
     * TO {"edit":{"all":false,"identifiers":["locale_a"]},"view":{"all":false,"identifiers":["locale_a", "locale_b"]}}
     */
    public function it_updatesPermissionsWhenSwitchingFromAllByDefaultToSpecificIdentifiers(
        SaverInterface $groupSaver,
        LocaleAccessManager $localeAccessManager,
        GroupInterface $group,
        LocaleInterface $localeA,
        LocaleInterface $localeB,
        LocaleInterface $localeC,
        GetActiveLocalesAccessesWithHighestLevel $getActiveLocalesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([self::DEFAULT_PERMISSION_VIEW => true]);
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_VIEW, false)->shouldBeCalled();
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_EDIT, false)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $getActiveLocalesAccessesWithHighestLevel->execute(42)->willReturn([
            'locale_a' => Attributes::EDIT_ITEMS,
            'locale_b' => Attributes::EDIT_ITEMS,
            'locale_c' => Attributes::VIEW_ITEMS,
        ]);

        $localeAccessManager->grantAccess($localeA, $group, Attributes::EDIT_ITEMS)->shouldNotBeCalled();
        $localeAccessManager->grantAccess($localeB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $localeAccessManager->revokeGroupAccess($localeC, $group)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => ['locale_a'],
            ],
            'view' => [
                'all' => false,
                'identifiers' => ['locale_a', 'locale_b'],
            ],
        ]);
    }
}
