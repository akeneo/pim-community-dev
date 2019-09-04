<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\QualityHighlights;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PurgePendingItemsTableCommand extends Command
{
    private const NAME = 'pimee:franklin-insights:quality-highlights:purge-pending-items-table';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct(self::NAME);

        $this->connection = $connection;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('This command will purge all the data that have to be send to Franklin Quality Highlight API endpoints.');

        $answer = $io->confirm('Are you sure you want to delete all the data that have to be send to Franklin');

        if($answer === false) {
            $io->note('Purge aborted, all the data have been kept.');

            exit(0);
        }

        $this->connection->query('TRUNCATE TABLE pimee_franklin_insights_quality_highlights_pending_items;');

        $io->success('Purge realized.');
    }
}
