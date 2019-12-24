<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Settings\Command\DeleteConnectionHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO: To remove
 * @deprecated
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:delete';

    /** @var DeleteConnectionHandler */
    private $deleteConnectionHandler;

    public function __construct(DeleteConnectionHandler $deleteConnectionHandler)
    {
        parent::__construct();

        $this->deleteConnectionHandler = $deleteConnectionHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $command = new \Akeneo\Apps\Application\Command\DeleteConnectionCommand('magento');
        $this->deleteConnectionHandler->handle($command);
    }
}
