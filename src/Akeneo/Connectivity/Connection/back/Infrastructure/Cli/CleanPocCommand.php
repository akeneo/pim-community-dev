<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanPocCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:clean-poc';
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        parent::__construct();
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new connection')
            ->addArgument(
                'code',
                InputArgument::REQUIRED,
                'Code of the connection.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $rmConnection = <<<SQL
DELETE FROM akeneo_connectivity_connection WHERE code = :code
SQL;
        $rmUser = <<<SQL
DELETE FROM oro_user WHERE username = :code
SQL;
        $rmRole = <<<SQL
DELETE FROM oro_access_role WHERE role = :role
SQL;

        $this->dbalConnection->executeUpdate($rmConnection, ['code' => strtr($code, '-', '_')]);
        $this->dbalConnection->executeUpdate($rmUser, ['code' => $code]);
        $this->dbalConnection->executeUpdate($rmRole, ['role' => $code . '-role']);

        return 0;
    }
}
