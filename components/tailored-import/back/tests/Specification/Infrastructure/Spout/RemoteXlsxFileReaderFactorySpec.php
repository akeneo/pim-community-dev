<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use Akeneo\Platform\TailoredImport\Infrastructure\Spout\CellsFormatter;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\RowCleaner;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileReader;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class RemoteXlsxFileReaderFactorySpec extends ObjectBehavior
{
    public function let(
        CellsFormatter $cellsFormatter,
        RowCleaner $rowCleaner,
        FilesystemProvider $filesystemProvider,
        FilesystemOperator $jobFilesystem
    ) {
        $filesystemProvider->getFilesystem('tailoredImport')->willReturn($jobFilesystem);
        $this->beConstructedWith($cellsFormatter, $filesystemProvider, $rowCleaner);
    }

    public function it_transfer_remote_file_to_local_file(FilesystemReader $jobFilesystem)
    {
        $stream = fopen(__DIR__ . '/../../../Common/simple_import.xlsx', 'r');
        $jobFilesystem->readStream('a/file/path.xlsx')->shouldBeCalled()->willReturn($stream);

        $this->create('a/file/path.xlsx')->shouldBeAnInstanceOf(XlsxFileReader::class);
        Assert::fileExists('/tmp/path.xlsx');

        $this->__destruct();
        Assert::false(file_exists('/tmp/path.xlsx'), 'The file "/tmp/path.xlsx" exist');
    }
}
