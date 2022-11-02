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

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeactivateCommand extends Command
{
    private Repository $repository;


    public function __construct(Repository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    protected function configure()
    {
        $this->setName('akeneo:sso:deactivate')
            ->setDescription('Deactivate the SSO authentication for the PIM');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $ssoConfiguration = $this->repository->find(Configuration::DEFAULT_CODE);
        } catch (ConfigurationNotFound $exception) {
            $io->success('There is no SSO authentication to deactivate.');
            return 0;
        }

        $ssoConfiguration = Configuration::fromArray(Configuration::DEFAULT_CODE, array_merge($ssoConfiguration->toArray(), [
            'isEnabled' => false,
        ]));
        $this->repository->save($ssoConfiguration);

        $io->success('The SSO authentication is deactivated.');

        return 0;
    }
}
