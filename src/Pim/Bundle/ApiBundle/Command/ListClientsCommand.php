<?php

namespace Pim\Bundle\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class ListClientsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:oauth-server:list-clients')
            ->setDescription('Lists all existing pairs of client id / secret for the web API')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('pim_api.repository.client');
        $clients = $repository->findAll();

        if (empty($clients)) {
            $output->writeln('No client is currently registered.');

            return 0;
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

        return 0;
    }
}
