<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Initializes PIM.ai user.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class InitPimAiUserCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'pimee:suggest-data:init-pimai-user';

    /** @var array */
    private $userData = [
        'username' => ProposalAuthor::USERNAME,
        'first_name' => 'PIM',
        'middle_name' => 'dot',
        'last_name' => 'ai',
        'email' => 'admin@akeneo.com',
        'enabled' => false,
        'user_default_locale' => 'en_US',
        'catalog_default_locale' => 'en_US',
    ];

    /** @var IdentifiableObjectRepositoryInterface */
    private $userRepository;

    /** @var SimpleFactoryInterface */
    private $userFactory;

    /** @var ObjectUpdaterInterface */
    private $userUpdater;

    /** @var SaverInterface */
    private $userSaver;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $userRepository
     * @param SimpleFactoryInterface                $userFactory
     * @param ObjectUpdaterInterface                $userUpdater
     * @param SaverInterface                        $userSaver
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $userRepository,
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        SaverInterface $userSaver,
        ValidatorInterface $validator
    ) {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->userSaver = $userSaver;
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Initializes the PIM.ai user');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $this->userRepository->findOneByIdentifier(ProposalAuthor::USERNAME)) {
            $output->writeln(
                sprintf(
                    '<info>User "%s" already exists. Aborting.</info>',
                    ProposalAuthor::USERNAME
                )
            );

            return;
        }

        $user = $this->userFactory->create();
        $this->userUpdater->update($user, array_merge($this->userData, ['password' => uniqid()]));
        $violations = $this->validator->validate($user);

        if (0 < count($violations)) {
            throw new \InvalidArgumentException($violations);
        }

        $this->userSaver->save($user);

        $output->writeln(sprintf('<info>Successfully created user "%s"</info>', ProposalAuthor::USERNAME));
    }
}
