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

namespace Akeneo\Pim\TrialEdition\Infrastructure\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Hslavich\OneloginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class SamlUserFactory implements SamlUserFactoryInterface
{
    private SamlUserFactoryInterface $baseUserFactory;

    private FeatureFlag $trialEditionFeature;

    private SimpleFactoryInterface $userFactory;

    private SaverInterface $userSaver;

    private ObjectUpdaterInterface $userUpdater;

    private ValidatorInterface $userValidator;

    private LoggerInterface $logger;

    public function __construct(
        SamlUserFactoryInterface $baseUserFactory,
        FeatureFlag $trialEditionFeature,
        SimpleFactoryInterface $userFactory,
        SaverInterface $userSaver,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $userValidator,
        LoggerInterface $logger
    ) {
        $this->baseUserFactory = $baseUserFactory;
        $this->trialEditionFeature = $trialEditionFeature;
        $this->userFactory = $userFactory;
        $this->userSaver = $userSaver;
        $this->userUpdater = $userUpdater;
        $this->userValidator = $userValidator;
        $this->logger = $logger;
    }

    public function createUser(SamlTokenInterface $token)
    {
        if (!$this->trialEditionFeature->isEnabled()) {
            return $this->baseUserFactory->createUser($token);
        }

        try {
            $user = $this->createUserFromSamlToken($token);
            $this->logger->info(sprintf("User '%s' created from Trial Edition SSO authentication.", $token->getUsername()));
        } catch (\Exception $exception) {
            $this->logger->error(
                'Unable to create user from Trial Edition SSO authentication',
                [
                    'error_code' => 'unable_to_create_trial_edition_user',
                    'error_message' => $exception->getMessage(),
                ]
            );

            throw new UnknownUserException($token->getUsername(), 'Unable to create user');
        }

        return $user;
    }

    private function createUserFromSamlToken(SamlTokenInterface $token)
    {
        $user = $this->userFactory->create();

        $this->userUpdater->update($user, [
            'username' => $token->getUsername(),
            'email' => $this->getUserAttribute('akeneo_email', $token),
            'password' => $this->generatePassword(),
            'first_name' => $this->getUserAttribute('akeneo_firstname', $token),
            'last_name' => $this->getUserAttribute('akeneo_lastname', $token),
            'groups' => ['All', 'Manager'],
            'roles' => ['ROLE_ADMINISTRATOR'],
            'catalog_default_locale' => 'en_US',
            'user_default_locale' => 'en_US',
            'catalog_default_scope' => 'ecommerce',
            'default_category_tree' => 'master',
        ]);

        $violations = $this->userValidator->validate($user);

        if ($violations->count() > 0) {
            throw new \InvalidArgumentException($this->formatViolations($violations));
        }

        $this->userSaver->save($user);

        return $user;
    }

    private function getUserAttribute(string $attributeName, SamlTokenInterface $token): string
    {
        Assert::true($token->hasAttribute($attributeName), sprintf("The SAML token has no attribute '%s'", $attributeName));
        $attribute = $token->getAttribute($attributeName);

        Assert::isArray($attribute, sprintf("Attribute '%s' is malformed", $attributeName));
        Assert::string($attribute[0], sprintf("Attribute '%s' value is not a string", $attributeName));

        return $attribute[0];
    }

    private function generatePassword(): string
    {
        return str_replace('-', '', Uuid::uuid4()->toString());
    }

    private function formatViolations(ConstraintViolationListInterface $violations): string
    {
        return implode(' ', array_map(
            fn ($violation) => sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()),
            iterator_to_array($violations)
        ));
    }
}
