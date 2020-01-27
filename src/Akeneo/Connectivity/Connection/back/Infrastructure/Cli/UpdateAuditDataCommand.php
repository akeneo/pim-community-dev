<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectEventDatesToRefreshQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-audit:update-data';

    /** @var UpdateProductEventCountHandler */
    private $updateProductEventCountHandler;

    /** @var DbalSelectEventDatesToRefreshQuery */
    private $selectEventDatesToRefreshQuery;

    public function __construct(
        UpdateProductEventCountHandler $updateProductEventCountHandler,
        DbalSelectEventDatesToRefreshQuery $selectEventDatesToRefreshQuery
    ) {
        parent::__construct();

        $this->updateProductEventCountHandler = $updateProductEventCountHandler;
        $this->selectEventDatesToRefreshQuery = $selectEventDatesToRefreshQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $datesToRefresh = $this->selectEventDatesToRefreshQuery->execute();
        if (!in_array(date('Y-m-d'), $datesToRefresh)) {
            $datesToRefresh[] = date('Y-m-d');
        }

        foreach ($datesToRefresh as $dateToRefresh) {
            $datetime = new \DateTime($dateToRefresh, new \DateTimeZone('UTC'));
            $datetime->setTime(0, 0, 0, 0);

            $command = new UpdateProductEventCountCommand($datetime->format('Y-m-d'));
            $this->updateProductEventCountHandler->handle($command);
        }

        return 0;
    }
}
