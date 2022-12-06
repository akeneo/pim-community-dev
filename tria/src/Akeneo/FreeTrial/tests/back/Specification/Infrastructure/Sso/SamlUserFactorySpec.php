<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\FreeTrial\Infrastructure\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SamlUserFactorySpec extends ObjectBehavior
{
    public function let(
        SamlUserFactoryInterface $baseUserFactory,
        FeatureFlags $featureFlags,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $userValidator,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith($baseUserFactory, $featureFlags, $userFactory, $userSaver, $userUpdater, $userValidator, $logger);
    }

    public function it_is_an_user_factory(): void
    {
        $this->shouldImplement(SamlUserFactoryInterface::class);
    }

    public function it_creates_an_user(
        FeatureFlags $featureFlags,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $userValidator,
        UserInterface $user,
        ConstraintViolationListInterface $violations
    ): void {
        $featureFlags->isEnabled('free_trial')->willReturn(true);

        $userFactory->create()->willReturn($user);

        $userUpdater->update($user, Argument::that(function (array $userData) {
            return isset($userData['username']) && $userData['username'] === 'an_user_name'
                && isset($userData['email']) && $userData['email'] === 'ziggy@akeneo.com'
                && isset($userData['first_name']) && $userData['first_name'] === 'Foo'
                && isset($userData['last_name']) && $userData['last_name'] === 'Bar'
            ;
        }))->shouldBeCalled();

        $userValidator->validate($user)->willReturn($violations);
        $violations->count()->willReturn(0);

        $userSaver->save($user)->shouldBeCalled();

        $this->createUser('an_user_name', [
            'akeneo_email' => ['ziggy@akeneo.com'],
            'akeneo_firstname' => ['Foo'],
            'akeneo_lastname' => ['Bar'],
        ])->shouldReturn($user);
    }

    public function it_calls_the_base_user_factory_if_the_free_trial_feature_is_disabled(
        SamlUserFactoryInterface $baseUserFactory,
        FeatureFlags $featureFlags,
        UserInterface $user,
    ): void {
        $featureFlags->isEnabled('free_trial')->willReturn(false);
        $baseUserFactory->createUser('an_user_name', [])->willReturn($user);

        $this->createUser('an_user_name', [])->shouldReturn($user);
    }

    public function it_throws_an_exception_if_an_attribute_is_missing(
        FeatureFlags $featureFlags,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        UserInterface $user,
    ): void {
        $featureFlags->isEnabled('free_trial')->willReturn(true);

        $userFactory->create()->willReturn($user);

        $userUpdater->update($user, Argument::any())->shouldNotBeCalled();
        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [
            'an_user_name',
            [
                'akeneo_email' => ['ziggy@akeneo.com'],
            ],
        ]);
    }

    public function it_throws_an_exception_if_an_attribute_value_is_not_a_string(
        FeatureFlags $featureFlags,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        UserInterface $user,
    ): void {
        $featureFlags->isEnabled('free_trial')->willReturn(true);

        $userFactory->create()->willReturn($user);

        $userUpdater->update($user, Argument::any())->shouldNotBeCalled();
        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [
            'an_user_name',
            [
                'akeneo_email' => ['ziggy@akeneo.com'],
                'akeneo_firstname' => [42],
            ],
        ]);
    }

    public function it_throws_an_exception_if_the_user_is_invalid(
        FeatureFlags $featureFlags,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $userValidator,
        UserInterface $user,
    ): void {
        $featureFlags->isEnabled('free_trial')->willReturn(true);

        $userFactory->create()->willReturn($user);

        $userUpdater->update($user, Argument::any())->shouldBeCalled();

        $violation = new ConstraintViolation('Invalid email', null, [], '', 'email', 'invalid.email');
        $violations = new ConstraintViolationList([$violation]);
        $userValidator->validate($user)->willReturn($violations);

        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [
            'an_user_name',
            [
                'akeneo_email' => ['invalid.email'],
                'akeneo_firstname' => ['Foo'],
                'akeneo_lastname' => ['Bar'],
            ],
        ]);
    }
}
