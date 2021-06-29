<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\SelectAllAuditableConnectionCodeQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeConnectionErrorsQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeConnectionErrorsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'akeneo:connectivity-connection:purge-error';

    private SelectAllAuditableConnectionCodeQuery $selectAllAuditableConnectionCodes;

    private PurgeConnectionErrorsQuery $purgeErrors;

    public function __construct(
        SelectAllAuditableConnectionCodeQuery $selectAllAuditableConnectionCodes,
        PurgeConnectionErrorsQuery $purgeErrors
    ) {
        parent::__construct();
        $this->selectAllAuditableConnectionCodes = $selectAllAuditableConnectionCodes;
        $this->purgeErrors = $purgeErrors;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codes = $this->selectAllAuditableConnectionCodes->execute();
        $this->purgeErrors->execute($codes);

        return 0;
    }
}
