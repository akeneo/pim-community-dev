<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Command;

use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Interactive command to create a PIM user.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateUserCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'pim:user:create';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(static::COMMAND_NAME)
            ->setDescription('Creates a PIM user.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("Please enter the user's information below.");

        $username = $this->askForUsername($input, $output);
        $password = $this->askForPassword($input, $output);
        $this->confirmPassword($input, $output, $password);
        $firstName = $this->askForFirstName($input, $output);
        $lastName = $this->askForLastName($input, $output);
        $email = $this->askForEmail($input, $output);
        $userDefaultLocaleCode = $this->askForUserDefaultLocaleCode($input, $output);
        $catalogDefaultLocaleCode = $this->askForCatalogDefaultLocaleCode($input, $output);
        $catalogDefaultScopeCode = $this->askForCatalogDefaultScopeCode($input, $output);
        $defaultTreeCode = $this->askForDefaultTreeCode($input, $output);

        $user = $this->getContainer()->get('pim_user.factory.user')->create();
        $this->getContainer()->get('pim_user.updater.user')->update(
            $user,
            [
                'username' => $username,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'user_default_locale' => $userDefaultLocaleCode,
                'catalog_default_locale' => $catalogDefaultLocaleCode,
                'catalog_default_scope' => $catalogDefaultScopeCode,
                'default_category_tree' => $defaultTreeCode,
            ]
        );

        $errors = $this->getContainer()->get('validator')->validate($user);
        if (0 < count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \InvalidArgumentException("The user creation failed :\n" . implode("\n", $errorMessages));
        }

        $this->addDefaultGroupTo($user);
        $this->addDefaultRoleTo($user);

        $this->getContainer()->get('pim_user.saver.user')->save($user);

        $output->writeln(sprintf("<info>User %s has been created.</info>", $username));
    }

    private function askForUsername(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Username : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The username is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForPassword(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Password (the input will be hidden) : ');
        $question
            ->setHidden(true)
            ->setHiddenFallback(false)
            ->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \InvalidArgumentException("The password is mandatory.");
                }

                return $answer;
            });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function confirmPassword(InputInterface $input, OutputInterface $output, string $password): void
    {
        $question = new Question('Confirm password : ');
        $question
            ->setHidden(true)
            ->setHiddenFallback(false)
            ->setValidator(function ($answer) use ($password) {
                if ($password !== $answer) {
                    throw new \InvalidArgumentException("The passwords must match.");
                }

                return $answer;
            });

        $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForFirstName(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('First name : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The first name is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForLastName(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Last name : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The last name is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForEmail(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Email : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The email is mandatory.");
            }

            if (false === filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Please enter a valid email address.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForUserDefaultLocaleCode(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('UI default locale code (e.g. "en_US") : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The UI default locale is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForCatalogDefaultLocaleCode(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Catalog default locale code (e.g. "en_US") : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The catalog default locale is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForCatalogDefaultScopeCode(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Catalog default scope code (e.g. "ecommerce") : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The catalog default scope is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForDefaultTreeCode(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('Default tree code (e.g. "master") : ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException("The default tree is mandatory.");
            }

            return $answer;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * Adds the default group "All" to the new user.
     */
    private function addDefaultGroupTo(UserInterface $user): void
    {
        $group = $this->getContainer()->get('pim_user.repository.group')->findOneByIdentifier(User::GROUP_DEFAULT);

        if (null === $group) {
            throw new \RuntimeException('Default user group not found.');
        }

        $user->addGroup($group);
    }

    /**
     * Adds the default role "ROLE_USER" to the new user.
     */
    private function addDefaultRoleTo(UserInterface $user): void
    {
        $role = $this->getContainer()->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);

        if (null === $role) {
            throw new \RuntimeException('Default user role not found.');
        }

        $user->addRole($role);
    }
}
