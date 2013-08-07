<?php

namespace Pim\Bundle\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Reindex data with a mandatory locale parameter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReindexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('pim:search:reindex')
             ->setDescription('Rebuild search index')
             ->addArgument('locale', InputArgument::REQUIRED, 'Locale to use to index data (for product title)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getArgument('locale');
        $this->getContainer()->get('pim_product.manager.product')->setLocale($locale);
        $this->getContainer()->get('pim_translation.listener.add_locale')->setLocale($locale);

        $output->writeln('Starting reindex task');
        $output->writeln('');

        /** @var $searchEngine \Oro\Bundle\SearchBundle\Engine\AbstractEngine */
        $searchEngine = $this->getContainer()->get('oro_search.search.engine');
        $recordsCount = $searchEngine->reindex();
        $output->writeln('');
        $output->writeln(sprintf('Total indexed items: %u', $recordsCount));
    }
}
