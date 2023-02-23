<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Doctrine\DBAL\Connection;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PopulateAccessTokenCommand extends Command
{
    protected static $defaultName = 'akeneo:test:populate-tokens';

    private const DEFAULT_BATCH_SIZE = 500_000;

    public function __construct(
        private readonly ClientManagerInterface $clientManager,
        private readonly CreateUserInterface $createUser,
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Populates access token')
            ->addArgument(
                'rows',
                InputArgument::REQUIRED,
                'Code of the connection.'
            )
            ->addOption(
                name: 'batchsize',
                mode: InputOption::VALUE_OPTIONAL,
                default: self::DEFAULT_BATCH_SIZE
            )
            ->addOption(
                name: 'refreshtoken',
                mode: InputOption::VALUE_OPTIONAL,
            )
            ->addOption(
                name: 'validtimestamp',
                mode: InputOption::VALUE_OPTIONAL,
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $validTimestamp = (bool) $input->getOption('validtimestamp');
        $populateRefreshToken = (bool) $input->getOption('refreshtoken');
        $rowCount = (int) $input->getArgument('rows');
        $batchSize = (int) $input->getOption('batchsize');

        $clientId = $this->getClientId();
        $userId = $this->getUserId();
        $timestamp = \time() + ($validTimestamp ? 100 : -123);
        $tableName = $populateRefreshToken ? 'pim_api_refresh_token' : 'pim_api_access_token';

        $output->writeln(\sprintf('Creating %s access token rows', $rowCount));
        $progressBar = new ProgressBar($output, $rowCount);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $remainder = $rowCount % $batchSize;
        $rowInserted = 0;

        while ($rowInserted < $rowCount) {
            $rowsToInsert =  $rowInserted < $rowCount - $batchSize ? $batchSize : $remainder;
            $rowsToInsert = $rowsToInsert === 0 ? $batchSize : $rowsToInsert;

            $values = '';
            $rowMax = $rowInserted + $rowsToInsert;

            for ($i = $rowInserted; $i < $rowMax; $i++) {
                $values .= "($clientId, $userId, '$timestamp-YTc4ZmM5M2Q3ZTJjNTM2NjliY2E2OWY4ZWIyMmU0NGE4Y2M2NmUxMmNmZmM2MzM2NTBiZTA3YTcyNGI5ODQyNA$i', 1673453538)";

                if ($i !== $rowMax - 1) {
                    $values .= ', ';
                }
            }

            $sqlQuery = "INSERT INTO $tableName (`client`, `user`, `token`, `expires_at`) VALUES $values;";

            $this->connection->executeStatement($sqlQuery);

            $rowInserted += $rowsToInsert;
            $progressBar->advance($rowsToInsert);
        }

        $progressBar->finish();
        $output->writeln('');
        $output->writeln('Done');

        return Command::SUCCESS;
    }

    private function getClientId(): int
    {
        $fosClient = $this->clientManager->createClient();
        /** @phpstan-ignore-next-line */
        $fosClient->setLabel('test_client');
        $fosClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);

        $this->clientManager->updateClient($fosClient);

        $sqlQuery = <<<SQL
            SELECT id FROM pim_api_client WHERE label = 'test_client'
        SQL;


        $id = (int) $this->connection->executeQuery($sqlQuery)->fetchOne();

        return $id;
    }

    private function getUserId(): int
    {
        $user = $this->createUser->execute('test_user', ' ', ' ', []);
        return $user->id();
    }
}
