<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Databases;

use Oro\Bundle\EntityExtendBundle\Databases\PostgresDatabase;

class PostgresDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PostgresDatabase */
    protected $classInstance;

    protected $db = 'test_db';
    protected $user = 'test_user';
    protected $tables = array('test_table_1', 'test_table_2');
    protected $pass = 'test_password';

    protected $file = 'dump.dump';

    protected $dump;
    protected $restore;

    public function setUp()
    {
        $this->classInstance = new PostgresDatabase($this->db, $this->user, $this->pass, $this->tables);

        $this->dump = 'PGPASSWORD="test_password" pg_dump -Fc --no-acl --no-owner -h "localhost" '.
            '-U "test_user" "test_db" -t "test_table_1" "test_table_2" > "dump.dump"';

        $this->restore = 'PGPASSWORD="test_password" pg_restore --clean --no-acl --no-owner -h "localhost" '.
            '-U "test_user" -d "test_db" "dump.dump"';
    }

    public function testDump()
    {
        $this->assertEquals($this->dump, $this->classInstance->dump($this->file));
    }

    public function testRestore()
    {
        $this->assertEquals($this->restore, $this->classInstance->restore($this->file));
    }

    public function testGetFileExtension()
    {
        $this->assertEquals('dump', $this->classInstance->getFileExtension());
    }
}
