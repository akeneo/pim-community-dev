<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllLocalesCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetLocaleReferenceFromCode;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetLocalesAccessesWithHighestLevel;
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
        GetAllLocalesCodes $getAllLocalesCodes,
        GetLocalesAccessesWithHighestLevel $getLocalesAccessesWithHighestLevel,
        GetLocaleReferenceFromCode $getLocaleReferenceFromCode,
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

        $getAllLocalesCodes->execute()->willReturn(['locale_a', 'locale_b', 'locale_c']);

        $getLocaleReferenceFromCode->execute('locale_a')->willReturn($localeA);
        $getLocaleReferenceFromCode->execute('locale_b')->willReturn($localeB);
        $getLocaleReferenceFromCode->execute('locale_c')->willReturn($localeC);

        $getLocalesAccessesWithHighestLevel->execute(42)->willReturn([]);

        $this->beConstructedWith(
            $localeAccessManager,
            $groupRepository,
            $groupSaver,
            $getAllLocalesCodes,
            $getLocalesAccessesWithHighestLevel,
            $getLocaleReferenceFromCode,
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
     * TO "edit":{"all":false,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
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
}
