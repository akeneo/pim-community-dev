<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Updater\UserUpdater;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class UserUpdaterSpec extends ObjectBehavior
{
    function let(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ObjectRepository $gridViewRepository,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith(
            $userManager,
            $categoryRepository,
            $localeRepository,
            $channelRepository,
            $roleRepository,
            $groupRepository,
            $gridViewRepository,
            $fileInfoRepository,
            $fileStorer,
            'file_storer',
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
        $user = new User();
        $user->addGroup(new Group('all'));

        $this->update(
            $user,
            [
                'property_name' => 'value',
                'other_property_name' => 'other_value',
            ]
        )->shouldReturn($this);

        Assert::eq( 'value', $user->getProperty('property_name'));
    }

    function it_updates_user_properties_in_properties_array()
    {
        $user = new User();
        $user->addGroup(new Group('all'));

        $this->update(
            $user,
            [
                'properties' => [ 'property_name' => 'value'],
                'other_property_name' => 'other_value',
            ]
        )->shouldReturn($this);

        Assert::eq( 'value', $user->getProperty('property_name'));
    }

    function it_throws_an_exception_if_it_is_not_a_whitelisted_property()
    {
        $user = new User();
        $user->addGroup(new Group('all'));

        $this
            ->shouldThrow(UnknownPropertyException::class)
            ->during('update',[$user, ['wrong_property' => 'value']]);
    }
}
