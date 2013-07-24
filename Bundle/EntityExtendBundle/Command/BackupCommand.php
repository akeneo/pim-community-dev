<?php

namespace Oro\Bundle\EntityExtendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Config;
use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityExtendBundle\Databases\DatabaseInterface;
use Oro\Bundle\EntityExtendBundle\Databases\MySQLDatabase;
use Oro\Bundle\EntityExtendBundle\Databases\PostgresDatabase;

class BackupCommand extends ContainerAwareCommand
{
    /**
     * @var DatabaseInterface
     */
    protected $database;

    protected $filePath;
    protected $fileName;

    /**
     * Console command configuration
     */
    protected function configure()
    {
        $this
            ->setName('oro:entity-extend:backup')
            ->setDescription('Backup database table(s)')
            ->addArgument('path', InputArgument::OPTIONAL, 'Override the configured backup path');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $basePath = $input->getArgument('path') ?: $this->getContainer()->getParameter('oro_entity_extend.backup');

        /** TODO: retrive DB parameters*/

        $dbms      = 'pdo_mysql';
        $database  = 'bap_dev';
        $user      = 'root';
        $password  = 'gbpltw';
        $tables    = array();
        $host      = 'localhost';

        switch ($dbms) {
            case 'pdo_mysql':
                $this->database = new MySQLDatabase($database, $user, $password, $tables, $host);
                break;
            case 'postgresql':
                //$this->database = new PostgresDatabase();
                break;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start backup');

        /** TODO:
         *      - check folder
         *      - check file
         *      - generate file name
         *      - generate file path
         *
         *      - provide ability to specify tables ????
         *          - get tables from EntityConfig (Provider)
         *
         *      - execute $database->dump
         * */

        print_r (get_class_methods($this->database));

        $output->writeln('Done');

//        $this->checkDumpFolder();
//
//        $this->fileName = date('Y-m-d_H-i-s') . '.' .$this->database->getFileExtension();
//        $this->filePath = $this->getDumpsPath() . $this->fileName;
//
//        if ($this->database->dump($this->filePath)) {
//            $this->line(sprintf('Database backup was successful. %s was saved in the dumps folder.', $this->fileName));
//
//        } else {
//            $this->line('Database backup failed');
//        }
    }

    /*protected function getOptions()
    {
        return array(
        );
    }*/

    /*protected function getDumpsPath()
    {
        return 'entities'.DIRECTORY_SEPARATOR.'Backup'.DIRECTORY_SEPARATOR;
    }*/

    /*protected function checkDumpFolder()
    {
        $dumpsPath = $this->getDumpsPath();
        if (!is_dir($dumpsPath)) {
            mkdir($dumpsPath);
        }
    }*/
}
