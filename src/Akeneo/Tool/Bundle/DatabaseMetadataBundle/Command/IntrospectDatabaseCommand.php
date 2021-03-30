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

use Symfony\Component\Console\Command\Command;
use Akeneo\Tool\Component\DatabaseMetadata\DatabaseInspector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntrospectDatabaseCommand extends Command
{
    protected static $defaultName = 'pimee:database:inspect';

    /** @var DatabaseInspector  */
    private $inspector;

    public function __construct(DatabaseInspector $inspector)
    {
        parent::__construct();
        $this->inspector = $inspector;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
       $this
         ->setDescription("Output the database structural informations.")
         ->addUsage('Dump database structure informations.') 
         ->setHelp('This command is used to either compare the PIM database structure with a reference or to troubleshoot problems.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach($this->inspector->getTableList() as $row) {
            $output->writeln(sprintf("%s | %s | %s", $row['table_name'], $row['table_type'], $row['auto_increment']));
        }
        foreach($this->inspector->getColumnInfo() as $row) {
            $output->writeln(sprintf(
                "%s | %s | %s | %s | %s",
                $row['table_name'],
                $row['column_name'],
                $row['is_nullable'],
                $row['column_type'],
                $row['column_key']
            ));
        }

        return 0;
    }
}