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

namespace Akeneo\FreeTrial\Infrastructure\Sso;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\User\UnknownUserException;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Hslavich\OneloginSamlBundle\Security\User\SamlUserFactoryInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class SamlUserFactory implements SamlUserFactoryInterface
{
    public function __construct(
        private SamlUserFactoryInterface $baseUserFactory,
        private FeatureFlags $featureFlags,
        private SimpleFactoryInterface $userFactory,
        private SaverInterface $userSaver,
        private ObjectUpdaterInterface $userUpdater,
        private ValidatorInterface $userValidator,
        private LoggerInterface $logger
    ) {
    }

    public function createUser($username, array $attributes = []): UserInterface
    {
        if (!$this->featureFlags->isEnabled('free_trial')) {
            return $this->baseUserFactory->createUser($username, $attributes);
        }

        try {
            $user = $this->createUserFromUsername($username, $attributes);
            $this->logger->info(sprintf("User '%s' created from free trial SSO authentication.", $username));
        } catch (\Exception $exception) {
            $this->logger->error(
                'Unable to create user from free trial SSO authentication',
                [
                    'error_code' => 'unable_to_create_free_trial_user',
                    'error_message' => $exception->getMessage(),
                ]
            );

            throw new UnknownUserException($username, 'Unable to create user');
        }

        return $user;
    }

    private function createUserFromUsername(string $username, array $attributes): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();

        $this->userUpdater->update($user, [
            'username' => $username,
            'email' => $this->getUserAttribute('akeneo_email', $attributes),
            'password' => $this->generatePassword(),
            'first_name' => $this->getUserAttribute('akeneo_firstname', $attributes),
            'last_name' => $this->getUserAttribute('akeneo_lastname', $attributes),
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

    private function getUserAttribute(string $attributeName, array $attributes): string
    {
        Assert::true(array_key_exists($attributeName, $attributes), sprintf("The SAML token has no attribute '%s'", $attributeName));
        $attribute = $attributes[$attributeName];

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
