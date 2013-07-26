<?php

namespace Oro\Bundle\EntityExtendBundle\Databases;

class MySQLDatabase implements DatabaseInterface
{
    protected $database;
    protected $user;
    protected $tables;
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
        $this->tables   = '"' . implode('" "', $tables) . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function dump($destinationFile)
    {
        $command = sprintf(
            'mysqldump --user="%s" --password="%s" --host="%s" "%s" %s > "%s"',
            $this->user,
            $this->password,
            $this->host,
            $this->database,
            $this->tables,
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
