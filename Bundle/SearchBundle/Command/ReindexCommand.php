<?php

namespace Oro\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update and reindex (automatically) fulltext-indexed table(s).
 * Use carefully on large datasets - do not run this task too often.
 *
 * @author magedan
 */
class ReindexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('oro:search:reindex')
             ->setDescription('Rebuild search index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting reindex task');
        $output->writeln('');

        /** @var $searchEngine \Oro\Bundle\SearchBundle\Engine\AbstractEngine */
        $searchEngine = $this->getContainer()->get('oro_search.search.engine');
        $recordsCount = $searchEngine->reindex();
        $output->writeln('');
        $output->writeln(sprintf('Total indexed items: %u', $recordsCount));
    }
}
