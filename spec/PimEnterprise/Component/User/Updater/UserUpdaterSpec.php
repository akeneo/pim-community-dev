<?php

namespace spec\PimEnterprise\Component\User\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
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
        IdentifiableObjectRepositoryInterface $categoryAssetRepository
    ) {
        $this->beConstructedWith(
            $userManager,
            $categoryRepository,
            $localeRepository,
            $channelRepository,
            $roleRepository,
            $groupRepository,
            $categoryAssetRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\User\Updater\UserUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldHaveType('Pim\Component\User\Updater\UserUpdater');
    }
}
