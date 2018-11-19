<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Updater\UserUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserUpdaterSpec extends ObjectBehavior
{
    function let(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryAssetRepository = null
    ) {
        $this->beConstructedWith(
            $userManager,
            $categoryRepository,
            $localeRepository,
            $channelRepository,
            $roleRepository,
            $groupRepository,
            $categoryAssetRepository,
            'property_name',
            'other_property_name'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_user_properties()
    {
        $this->update(
            [
                'property_name' => 'value',
                'other_property_name' => 'other_value',
            ]
        )->shouldReturn($this);
    }
}
