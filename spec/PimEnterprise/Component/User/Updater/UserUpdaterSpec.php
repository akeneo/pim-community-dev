<?php

namespace spec\PimEnterprise\Component\User\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class UserUpdaterSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $userUpdater,
        IdentifiableObjectRepositoryInterface $categoryAssetRepository
    ) {
        $this->beConstructedWith(
            $userUpdater,
            $categoryAssetRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\User\Updater\UserUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldHaveType(ObjectUpdaterInterface::class);
    }
}
