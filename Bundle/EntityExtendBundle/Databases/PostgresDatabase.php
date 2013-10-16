<?php

namespace Oro\Bundle\EntityExtendBundle\Databases;

class PostgresDatabase implements DatabaseInterface
{
    protected $database;
    protected $user;
    protected $tables;
    protected $password;
    protected $host;

    /**
     * @param $database
     * @param $user
     * @param $tables
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
            'PGPASSWORD="%s" pg_dump -Fc --no-acl --no-owner -h "%s" -U "%s" "%s" -t %s > "%s"',
            $this->password,
            $this->host,
            $this->user,
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
            'PGPASSWORD="%s" pg_restore --clean --no-acl --no-owner -h "%s" -U "%s" -d "%s" "%s"',
            $this->password,
            $this->host,
            $this->user,
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
        return 'dump';
    }
}
