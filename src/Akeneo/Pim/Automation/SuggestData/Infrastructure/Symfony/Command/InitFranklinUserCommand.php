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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Initializes Franklin user.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class InitFranklinUserCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'pimee:suggest-data:init-franklin-user';

    /** @var array */
    private $userData = [
        'username' => ProposalAuthor::USERNAME,
        'first_name' => 'Franklin',
        'last_name' => 'Insights',
        'email' => 'admin@akeneo.com',
        'enabled' => false,
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

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $userRepository
     * @param SimpleFactoryInterface $userFactory
     * @param ObjectUpdaterInterface $userUpdater
     * @param SaverInterface $userSaver
     * @param ValidatorInterface $validator
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $userRepository,
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        SaverInterface $userSaver,
        ValidatorInterface $validator,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->userSaver = $userSaver;
        $this->validator = $validator;
        $this->localeRepository = $localeRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Initializes the Franklin user');
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
        $this->userUpdater->update($user, $this->getUserData());
        $violations = $this->validator->validate($user);

        if (0 < count($violations)) {
            foreach ($violations as $violation) {
                $output->writeln('<error>' . $violation->__toString() . '</error>');
            }
            throw new \InvalidArgumentException();
        }

        $this->userSaver->save($user);

        $output->writeln(sprintf('<info>Successfully created user "%s"</info>', ProposalAuthor::USERNAME));
    }

    /**
     * @return array
     */
    private function getUserData(): array
    {
        $activeLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();
        $userLocale = in_array('en_US', $activeLocaleCodes) ? 'en_US' : $activeLocaleCodes[0];

        return array_merge(
            $this->userData,
            [
                'password' => uniqid(),
                'user_default_locale' => $userLocale,
                'catalog_default_locale' => $userLocale,
            ]
        );
    }
}
