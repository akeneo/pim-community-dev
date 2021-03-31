<?php declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Command;

use Akeneo\Tool\Component\DatabaseMetadata\DatabaseInspector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IntrospectDatabaseCommand extends Command
{
    public const DEFAULT_FILENAME = 'akeneo_pim_db.metadata.txt';

    protected static $defaultName = 'pimee:database:inspect';

    /** @var DatabaseInspector  */
    private $inspector;

    public function __construct(DatabaseInspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function configure()
    {
       $this
         ->setDescription("Output the database structural informations.")
         ->addUsage('Dump database structure informations.') 
         ->setHelp('This command is used to either compare the PIM database structure with a reference or to troubleshoot problems.')
         ->addOption('db-name', 'd', InputOption::VALUE_REQUIRED, 'The database name when different from "akeneo_pim".', 'akeneo_pim')
         ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, sprintf('When present, this option makes the tool to save the output in a file. If the name of the file is not provided, it will default to "%s".', self::DEFAULT_FILENAME), false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db_name = $input->getOption('db-name');

        if ($input->getOption('file') !== false) {
            $filesystem = new Filesystem();

            if (null === $input->getOption('file')) {
                $filename = self::DEFAULT_FILENAME;
            } else {
                $filename = $input->getOption('file');
            }

            if (file_exists($filename)) {
                $filesystem->remove($filename);
            }
        }

        foreach($this->inspector->getTableList($db_name) as $row) {
            $line = sprintf("%s | %s | %s\n", $row['table_name'], $row['table_type'], $row['auto_increment']);

            if (isset($filesystem)) {
                $filesystem->appendToFile($filename, $line);
            } else {
                $output->write($line);
            }
        }
        foreach($this->inspector->getColumnInfo($db_name) as $row) {
            $line = sprintf(
                "%s | %s | %s | %s | %s\n",
                $row['table_name'],
                $row['column_name'],
                $row['is_nullable'],
                $row['column_type'],
                $row['column_key']
            );

            if (isset($filesystem)) {
                $filesystem->appendToFile($filename, $line);
            } else {
                $output->write($line);
            }
        }

        return 0;
    }
}