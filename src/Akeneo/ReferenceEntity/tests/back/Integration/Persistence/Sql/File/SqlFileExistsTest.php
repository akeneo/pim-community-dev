<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\File;

use Akeneo\ReferenceEntity\Domain\Query\File\FileExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SqlFileExistsTest extends SqlIntegrationTestCase
{
    /** @var FileExistsInterface */
    private $fileExists;

    /** @var SaverInterface */
    private $fileSaver;

    public function setUp(): void
    {
        parent::setUp();

        $this->fileExists = $this->get('akeneo_referenceentity.infrastructure.persistence.query.file_exists');
        $this->fileSaver = $this->get('akeneo_file_storage.saver.file');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();

        $this->loadFile();
    }

    private function loadFile()
    {
        $file = new FileInfo();
        $file->setKey('files/starck.jpg');
        $file->setMimeType('file/jpg');
        $file->setOriginalFilename('starck.jpg');
        $file->setSize(1024);
        $file->setExtension('jpg');
        $file->setHash(sha1('Starck file'));
        $file->setStorage('catalogStorage');

        $this->fileSaver->save($file);
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_file_exists()
    {
        $fileExists = ($this->fileExists)('files/starck.jpg');

        $this->assertTrue($fileExists);
    }

    /**
     * @test
     */
    public function it_returns_false_if_no_file_was_found()
    {
        $fileExists = ($this->fileExists)('files/no_file.jpg');

        $this->assertFalse($fileExists);
    }
}
