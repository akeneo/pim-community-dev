<?php

namespace Oro\Bundle\EntityExtendBundle\Databases;

use Oro\Bundle\EntityExtendBundle\Console;

class PostgresDatabase implements DatabaseInterface
{
    protected $console;
    protected $database;
    protected $user;
    protected $password;
    protected $host;

    /**
     * @param Console $console
     * @param $database
     * @param $user
     * @param $password
     * @param string $host
     */
    public function __construct(Console $console, $database, $user, $password, $host = 'localhost')
    {
        $this->console  = $console;
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
            'PGPASSWORD="%s" pg_dump -Fc --no-acl --no-owner -h "%s" -U "%s" "%s" > "%s"',
            $this->password,
            $this->host,
            $this->user,
            $this->database,
            $destinationFile
        );

        return $this->console->run($command);
    }

    /**
     * {@inheritdoc}
     */
    public function restore($sourceFile)
    {
        $command = sprintf(
            'PGPASSWORD="%s" pg_restore --verbose --clean --no-acl --no-owner -h "%s" -U "%s" -d "%s" "%s"',
            $this->password,
            $this->host,
            $this->user,
            $this->database,
            $sourceFile
        );

        return $this->console->run($command);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return 'dump';
    }
}
