<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\Provider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ProviderSpec extends ObjectBehavior
{

    function let(
        UserRepositoryInterface $userRepository,
        Repository $configRepository
    )
    {
        $this->beConstructedWith(
            $userRepository,
            $configRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Provider::class);
    }

    function it_is_a_user_provider()
    {
        $this->shouldImplement(UserProviderInterface::class);
    }

    function it_throws_an_exception_if_sso_is_disabled($userRepository, $configRepository)
    {
        $ssoConfiguration = $this->getDisabledConfiguration();
        $configRepository->find('authentication_sso')->shouldBeCalled()->willReturn($ssoConfiguration);

        $userRepository->findOneBy(Argument::cetera())->shouldNotBeCalled();
        $this->shouldThrow(new UsernameNotFoundException('SSO feature is not enabled, let another UserProvider do its job.'))
            ->during('loadUserByUsername', ['julia@example.com']);
    }

    function it_throws_an_exception_if_user_does_not_exist($userRepository, $configRepository)
    {
        $ssoConfiguration = $this->getEnabledConfiguration();
        $configRepository->find('authentication_sso')->shouldBeCalled()->willReturn($ssoConfiguration);

        $userRepository->findOneBy(['username' => 'unknown@example.com'])->willReturn(null);
        $this->shouldThrow(UsernameNotFoundException::class)
            ->during('loadUserByUsername', ['unknown@example.com']);
    }

    function it_loads_a_user_by_its_username($userRepository, $configRepository)
    {
        $ssoConfiguration = $this->getEnabledConfiguration();
        $configRepository->find('authentication_sso')->shouldBeCalled()->willReturn($ssoConfiguration);

        $julia = new User('julia@example.com', 'kitten123');

        $userRepository->findOneBy(['username' => 'julia@example.com'])->willReturn($julia);
        $this->loadUserByUsername('julia@example.com')->shouldReturn($julia);
    }

    function it_refreshes_a_user($userRepository, $configRepository)
    {
        $ssoConfiguration = $this->getEnabledConfiguration();
        $configRepository->find('authentication_sso')->shouldBeCalled()->willReturn($ssoConfiguration);

        $julia = new User('julia@example.com', 'kitten123');

        $userRepository->findOneBy(['username' => 'julia@example.com'])->willReturn($julia);
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    private function getEnabledConfiguration(): Configuration
    {
        return $this->getConfiguration(true);
    }

    private function getDisabledConfiguration(): Configuration
    {
        return $this->getConfiguration(false);
    }

    private function getConfiguration(bool $enabled): Configuration
    {
        return new Configuration(
            new Code('authentication_sso'),
            new IsEnabled($enabled),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('certificate'),
                new Certificate('private_key')
            )
        );
    }
}
