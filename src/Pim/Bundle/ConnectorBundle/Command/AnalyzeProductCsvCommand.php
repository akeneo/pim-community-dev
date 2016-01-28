<?php

namespace Pim\Bundle\ConnectorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides some statistics on a product CSV file
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnalyzeProductCsvCommand extends ContainerAwareCommand
{
    /** @staticvar string */
    const DEFAULT_DELIMITER = ";";

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:connector:analyzer:csv-products')
            ->setDescription('Analyze the products CSV')
            ->addOption(
                'csv-delimiter',
                'd',
                InputOption::VALUE_REQUIRED,
                'If set, will be used as the CSV delimiter'
            )
            ->addArgument(
                'product-csv-file',
                InputArgument::REQUIRED,
                'Products CSV file'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productCsvFile = $input->getArgument('product-csv-file');

        $fs = new Filesystem();

        if (!$fs->exists($productCsvFile)) {
            throw new \InvalidArgumentException(
                sprintf("Unable to find %s", $productCsvFile)
            );
        }

        $delimiter = static::DEFAULT_DELIMITER;

        if (null !== $input->getOption('csv-delimiter')) {
            $delimiter = $input->getOption('csv-delimiter');
        }

        $output->writeln(
            sprintf(
                '<info>Analyzing Product CSV %s...</info>',
                $productCsvFile
            )
        );

        $reader = $this->getProductCsvReader();
        $reader->setDelimiter($delimiter);
        $reader->setFilePath($productCsvFile);

        $stats = $this->getProductAnalyzer()->analyze($reader);

        $output->writeln([
            sprintf('<info>Columns Count:  %10s</info>', number_format($stats['columns_count'])),
            sprintf('<info>Products Count: %10s</info>', number_format($stats['products']['count'])),
            sprintf('<info>Values (or fields) Count:   %10s</info>', number_format($stats['products']['values_count'])),
            '<info>Values per product:</info>',
            sprintf('<info>  Average: %5s</info>', number_format($stats['products']['values_per_product']['average'])),
            sprintf(
                '<info>  Min:     %5s (line %d)</info>',
                number_format($stats['products']['values_per_product']['min']['count']),
                $stats['products']['values_per_product']['min']['line_number']
            ),
            sprintf(
                '<info>  Max:     %5s (line %d)</info>',
                number_format($stats['products']['values_per_product']['max']['count']),
                $stats['products']['values_per_product']['max']['line_number']
            )
        ]);

        $output->writeln([
            "",
            "<info>DISCLAIMER: the values and fields related statistics do not take into account<info>",
            "<info>multi-columns values (like metrics or prices) into account.</info>",
            "<info>The provided figures is shown to give a global view of the data volume present in the file.</info>"
        ]);

        return 0;
    }

    protected function getProductAnalyzer()
    {
        return $this->getContainer()->get('pim_connector.analyzer.product');
    }

    protected function getProductCsvReader()
    {
        return $this->getContainer()->get('pim_connector.reader.file.csv_product');
    }
}
