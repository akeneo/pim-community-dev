<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Databases;

use Oro\Bundle\EntityExtendBundle\Databases\MySQLDatabase;

class MySQLDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MySQLDatabase */
    protected $classInstance;

    protected $db = 'test_db';
    protected $user = 'test_user';
    protected $tables = array('test_table_1', 'test_table_2');
    protected $pass = 'test_password';

    protected $file = 'dump.sql';

    protected $dump;
    protected $restore;

    public function setUp()
    {
        $this->classInstance = new MySQLDatabase($this->db, $this->user, $this->pass, $this->tables);

        $this->dump = 'mysqldump --user="test_user" --password="test_password" --host="localhost" '.
            '"test_db" "test_table_1" "test_table_2" > "dump.sql"';

        $this->restore = 'mysql --user="test_user" --password="test_password" --host="localhost" '.
            '"test_db" < "dump.sql"';
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
        $this->assertEquals('sql', $this->classInstance->getFileExtension());
    }
}
