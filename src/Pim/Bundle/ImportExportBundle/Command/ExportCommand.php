<?php

namespace Pim\Bundle\ImportExportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('export')
            ->addArgument('alias')
            ->addArgument('format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $exporter  = $container->get('pim_import_export.exporter_registry')->getExporter($input->getArgument('alias'));
        $exporter->export($input->getArgument('format'));
    }
}

