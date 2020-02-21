<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\PurgeReadProductTableQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeReadProductTableCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:purge-read-product';

    /** @var PurgeReadProductTableQuery */
    private $purgeQuery;

    public function __construct(PurgeReadProductTableQuery $purgeQuery)
    {
        $this->purgeQuery = $purgeQuery;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription(
            'Purge table where products read through the API are stored. Keeps data for the 8 last days.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $before = new \DateTime('now - 10 days', new \DateTimeZone('UTC'));
        $count = $this->purgeQuery->execute($before);
        $output->writeln(sprintf('%s rows have been deleted.', $count));

        return 0;
    }
}
