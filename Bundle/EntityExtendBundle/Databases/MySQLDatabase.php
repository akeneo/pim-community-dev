<?php

namespace Oro\Bundle\EntityExtendBundle\Databases;

use Oro\Bundle\EntityExtendBundle\Console;

class MySQLDatabase implements DatabaseInterface
{
    protected $tables;
    protected $database;
    protected $user;
    protected $password;
    protected $host;

    /**
     * @param array $tables
     * @param $database
     * @param $user
     * @param $password
     * @param string $host
     */
    public function __construct($database, $user, $password, $tables = array(), $host = 'localhost')
    {
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
    }

    /**
     * {@inheritdoc}
     */
    public function dump($destinationFile)
    {
        $command = sprintf(
            'mysqldump --user="%s" --password="%s" --host="%s" "%s" > "%s"',
            $this->user,
            $this->password,
            $this->host,
            $this->database,
            $destinationFile
        );

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function restore($sourceFile)
    {
        $command = sprintf(
            'mysql --user="%s" --password="%s" --host="%s" "%s" < "%s"',
            $this->user,
            $this->password,
            $this->host,
            $this->database,
            $sourceFile
        );

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return 'sql';
    }
}