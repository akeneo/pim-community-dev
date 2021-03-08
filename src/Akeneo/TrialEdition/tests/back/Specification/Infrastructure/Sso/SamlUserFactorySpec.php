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

namespace Specification\Akeneo\Pim\TrialEdition\Infrastructure\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
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
        FeatureFlag $trialEditionFeature,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $userValidator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($baseUserFactory, $trialEditionFeature, $userFactory, $userSaver, $userUpdater, $userValidator, $logger);
    }

    public function it_is_an_user_factory()
    {
        $this->shouldImplement(SamlUserFactoryInterface::class);
    }

    public function it_creates_an_user_from_a_saml_token(
        $trialEditionFeature,
        $userFactory,
        $userSaver,
        $userUpdater,
        $userValidator,
        SamlTokenInterface $token,
        UserInterface $user,
        ConstraintViolationListInterface $violations
    ) {
        $trialEditionFeature->isEnabled()->willReturn(true);

        $userFactory->create()->willReturn($user);
        $token->getUsername()->willReturn('an_user_name');

        $userAttributes = [
            'akeneo_email' => 'ziggy@akeneo.com',
            'akeneo_firstname' => 'Foo',
            'akeneo_lastname' => 'Bar'
        ];
        foreach ($userAttributes as $attributeName => $attributeValue) {
            $token->hasAttribute($attributeName)->willReturn(true);
            $token->getAttribute($attributeName)->willReturn([$attributeValue]);
        }

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

        $this->createUser($token)->shouldReturn($user);
    }

    public function it_calls_the_base_user_factory_if_the_trial_edition_feature_is_disabled(
        $baseUserFactory,
        $trialEditionFeature,
        SamlTokenInterface $token
    ) {
        $trialEditionFeature->isEnabled()->willReturn(false);
        $baseUserFactory->createUser($token)->shouldBeCalled();

        $this->createUser($token);
    }

    public function it_throws_an_exception_if_an_attribute_is_missing_in_the_token(
        $trialEditionFeature,
        $userFactory,
        $userSaver,
        $userUpdater,
        SamlTokenInterface $token,
        UserInterface $user
    ) {
        $trialEditionFeature->isEnabled()->willReturn(true);

        $userFactory->create()->willReturn($user);
        $token->getUsername()->willReturn('an_user_name');

        $token->hasAttribute('akeneo_email')->willReturn(true);
        $token->getAttribute('akeneo_email')->willReturn(['ziggy@akeneo.com']);
        $token->hasAttribute('akeneo_firstname')->willReturn(false);

        $userUpdater->update($user, Argument::any())->shouldNotBeCalled();
        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [$token]);
    }

    public function it_throws_an_exception_if_an_attribute_is_malformed_in_the_token(
        $trialEditionFeature,
        $userFactory,
        $userSaver,
        $userUpdater,
        SamlTokenInterface $token,
        UserInterface $user
    ) {
        $trialEditionFeature->isEnabled()->willReturn(true);

        $userFactory->create()->willReturn($user);
        $token->getUsername()->willReturn('an_user_name');

        $token->hasAttribute('akeneo_email')->willReturn(true);
        $token->getAttribute('akeneo_email')->willReturn(['ziggy@akeneo.com']);
        $token->hasAttribute('akeneo_firstname')->willReturn(true);
        $token->getAttribute('akeneo_firstname')->willReturn('Foo');

        $userUpdater->update($user, Argument::any())->shouldNotBeCalled();
        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [$token]);
    }

    public function it_throws_an_exception_if_an_attribute_value_is_not_a_string(
        $trialEditionFeature,
        $userFactory,
        $userSaver,
        $userUpdater,
        SamlTokenInterface $token,
        UserInterface $user
    ) {
        $trialEditionFeature->isEnabled()->willReturn(true);

        $userFactory->create()->willReturn($user);
        $token->getUsername()->willReturn('an_user_name');

        $token->hasAttribute('akeneo_email')->willReturn(true);
        $token->getAttribute('akeneo_email')->willReturn(['ziggy@akeneo.com']);
        $token->hasAttribute('akeneo_firstname')->willReturn(true);
        $token->getAttribute('akeneo_firstname')->willReturn([42]);

        $userUpdater->update($user, Argument::any())->shouldNotBeCalled();
        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [$token]);
    }

    public function it_throws_an_exception_if_the_user_is_invalid(
        $trialEditionFeature,
        $userFactory,
        $userSaver,
        $userUpdater,
        $userValidator,
        SamlTokenInterface $token,
        UserInterface $user
    ) {
        $trialEditionFeature->isEnabled()->willReturn(true);

        $userFactory->create()->willReturn($user);
        $token->getUsername()->willReturn('an_user_name');

        $userAttributes = [
            'akeneo_email' => 'invalid.email',
            'akeneo_firstname' => 'Foo',
            'akeneo_lastname' => 'Bar'
        ];
        foreach ($userAttributes as $attributeName => $attributeValue) {
            $token->hasAttribute($attributeName)->willReturn(true);
            $token->getAttribute($attributeName)->willReturn([$attributeValue]);
        }

        $userUpdater->update($user, Argument::any())->shouldBeCalled();

        $violation = new ConstraintViolation('Invalid email', null, [], '', 'email', 'invalid.email');
        $violations = new ConstraintViolationList([$violation]);
        $userValidator->validate($user)->willReturn($violations);

        $userSaver->save($user)->shouldNotBeCalled();

        $this->shouldThrow(UnknownUserException::class)->during('createUser', [$token]);
    }
}
