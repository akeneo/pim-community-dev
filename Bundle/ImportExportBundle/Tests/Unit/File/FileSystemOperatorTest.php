<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\File;

use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;

class FileSystemOperatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $existingDir = 'existing';

    /**
     * @var string
     */
    protected $newDir = 'new';

    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = __DIR__ . '/fixtures';
    }

    protected function tearDown()
    {
        $newDirPath = $this->cacheDir . DIRECTORY_SEPARATOR . $this->newDir;
        if (is_dir($newDirPath)) {
            @rmdir($newDirPath);
        }
    }

    /**
     * @dataProvider dirDataProvider
     * @param string $dir
     */
    public function testGetTemporaryDirectory($dir)
    {
        $fs = new FileSystemOperator($this->cacheDir, $dir);
        $expectedDir = $this->cacheDir . DIRECTORY_SEPARATOR . $dir;
        $this->assertEquals($expectedDir, $fs->getTemporaryDirectory());
        $this->assertFileExists($expectedDir);
    }

    public function dirDataProvider()
    {
        return array(
            array($this->existingDir),
            array($this->newDir),
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Can't read file unknown.csv
     */
    public function testGetTemporaryFileException()
    {
        $fs = new FileSystemOperator($this->cacheDir, $this->existingDir);
        $fs->getTemporaryFile('unknown.csv');
    }

    public function testGetTemporaryFile()
    {
        $fs = new FileSystemOperator($this->cacheDir, $this->existingDir);
        $this->assertInstanceOf('\SplFileObject', $fs->getTemporaryFile('file.csv'));
    }

    public function testGenerateTemporaryFileName()
    {
        $fs = new FileSystemOperator($this->cacheDir, $this->existingDir);
        $fileName = $fs->generateTemporaryFileName('test', 'ext');
        $this->assertStringEndsWith('ext', $fileName);
        $this->assertContains(DIRECTORY_SEPARATOR . 'test', $fileName);
        $this->assertContains(date('Y_m_d_H_i_'), $fileName);
    }
}
