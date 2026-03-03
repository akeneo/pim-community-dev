<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Command;

use Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\PurgeAuditErrorQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeAuditErrorCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'akeneo:connectivity-audit:purge-error-count';

    public function __construct(private PurgeAuditErrorQuery $purgeAuditErrorsQuery)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $before = new \DateTimeImmutable('now - 10 days', new \DateTimeZone('UTC'));
        $before = $before->setTime((int) $before->format('H'), 0, 0);

        $this->purgeAuditErrorsQuery->execute($before);

        return Command::SUCCESS;
    }
}
