<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Command;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command lists all existing pairs of client id / secret for the web API.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListClientsCommand extends Command
{
    protected static $defaultName = 'pim:oauth-server:list-clients';
    protected static $defaultDescription = 'Lists all existing pairs of client id / secret for the web API';

    private EntityRepository $clientRepository;

    public function __construct(EntityRepository $clientRepository)
    {
        parent::__construct();

        $this->clientRepository = $clientRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clients = $this->clientRepository->findAll();

        if (empty($clients)) {
            $output->writeln('No client is currently registered.');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['client id', 'secret', 'label']);

        foreach ($clients as $client) {
            $table->addRow([
                $client->getPublicId(),
                $client->getSecret(),
                $client->getLabel(),
            ]);
        }

        $table->render($output);

        return Command::SUCCESS;
    }
}
