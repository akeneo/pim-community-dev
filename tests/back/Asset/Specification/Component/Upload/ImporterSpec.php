<?php

namespace Specification\Akeneo\Asset\Component\Upload;

use Akeneo\Asset\Component\Upload\Importer;
use Akeneo\Asset\Component\Upload\ImporterInterface;
use Akeneo\Tool\Component\FileStorage\File\FileFetcher;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Upload\ParsedFilenameInterface;
use Akeneo\Asset\Component\Upload\UploadCheckerInterface;
use Akeneo\Asset\Component\Upload\UploadContext;
use Prophecy\Argument;

class ImporterSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        UploadCheckerInterface $uploadChecker,
        ParsedFilenameInterface $fooParsed,
        ParsedFilenameInterface $barParsed,
        FileStorerInterface $fileStorer,
        FilesystemProvider $filesystemProvider,
        FileFetcher $fileFetcher
    ) {
        $this->createUploadBaseDirectory();

        $fooParsed->getAssetCode()->willReturn('foo');
        $fooParsed->getLocaleCode()->willReturn(null);
        $barParsed->getAssetCode()->willReturn('bar');
        $barParsed->getLocaleCode()->willReturn(null);

        $uploadChecker->getParsedFilename(Argument::containingString('foo.png'))->willReturn($fooParsed);
        $uploadChecker->getParsedFilename(Argument::containingString('bar.png'))->willReturn($barParsed);

        $this->beConstructedWith($uploadChecker, $fileStorer, $fileFetcher, $filesystemProvider);
    }

    function letGo()
    {
        $this->removeUploadBaseDirectory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Importer::class);
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_imports_files_from_the_upload_filesystem(
        UploadCheckerInterface $uploadChecker,
        FileStorerInterface $fileStorer,
        FileFetcher $fileFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $filesystemProvider->getFilesystem('tmpAssetUpload')->willReturn($filesystem);

        $uploadContext = new UploadContext('/tmp', 'julia');
        $uploadTmpDirectory = $uploadContext->getTemporaryImportDirectoryRelativePath();

        $barImportPath = $uploadTmpDirectory . '/bar.png';
        $fooImportPath = $uploadTmpDirectory . '/foo.png';

        $filesystem->listContents($uploadTmpDirectory)->willReturn([
            [
                'path' => $barImportPath,
                'dirname' => 'mass_upload_tmp/julia',
                'basename' => 'bar.png',
                'extension' => 'png',
                'filename' => 'bar',
                'timestamp' => 1568212566,
                'size' => '130364',
                'type' => 'file',
            ],
            [
                'path' => $fooImportPath,
                'dirname' => 'mass_upload_tmp/julia',
                'basename' => 'foo.png',
                'extension' => 'png',
                'filename' => 'foo',
                'timestamp' => 1568212567,
                'size' => '26736',
                'type' => 'file',
            ]
        ]);

        $fileFetcher->fetch($filesystem, $barImportPath)->shouldBeCalled();
        $fileFetcher->fetch($filesystem, $fooImportPath)->shouldBeCalled();
        $filesystem->has($barImportPath)->willReturn(true);
        $filesystem->has($fooImportPath)->willReturn(true);
        $filesystem->delete($barImportPath)->shouldBeCalled();
        $filesystem->delete($fooImportPath)->shouldBeCalled();

        $this->import($uploadContext)
            ->shouldReturn([
                ['file' => 'bar.png', 'error' => null],
                ['file' => 'foo.png', 'error' => null],
            ]);
    }

    function it_imports_files_for_given_file_names(
        UploadCheckerInterface $uploadChecker,
        FileStorerInterface $fileStorer,
        FileFetcher $fileFetcher,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem
    ) {
        $this->beConstructedWith($uploadChecker, $fileStorer, $fileFetcher, $filesystemProvider);

        $filesystemProvider->getFilesystem('tmpAssetUpload')->willReturn($filesystem);

        $uploadContext = new UploadContext('/tmp', 'julia');
        $uploadTmpDirectory = $uploadContext->getTemporaryImportDirectoryRelativePath();

        $barImportPath = $uploadTmpDirectory . '/bar.png';
        $fooImportPath = $uploadTmpDirectory . '/foo.png';

        $filesystem->listContents($uploadTmpDirectory)->willReturn([
            [
                'path' => $barImportPath,
                'dirname' => 'mass_upload_tmp/julia',
                'basename' => 'bar.png',
                'extension' => 'png',
                'filename' => 'bar',
                'timestamp' => 1568212566,
                'size' => '130364',
                'type' => 'file',
            ],
            [
                'path' => $fooImportPath,
                'dirname' => 'mass_upload_tmp/julia',
                'basename' => 'foo.png',
                'extension' => 'png',
                'filename' => 'foo',
                'timestamp' => 1568212567,
                'size' => '26736',
                'type' => 'file',
            ]
        ]);

        $fileFetcher->fetch($filesystem, $barImportPath)->shouldBeCalled();
        $fileFetcher->fetch($filesystem, $fooImportPath)->shouldNotBeCalled();
        $filesystem->has($barImportPath)->willReturn(true);
        $filesystem->delete($barImportPath)->shouldBeCalled();

        $this->import($uploadContext, ['bar.png'])
            ->shouldReturn([
                ['file' => 'bar.png', 'error' => null],
            ]);
    }

    function it_gets_imported_files(UploadContext $uploadContext)
    {
        $sourceDirectory = $this->createSourceDirectory();
        $importDirectory = $this->createImportDirectory();

        $uploadContext->getTemporaryUploadDirectory()->willReturn($sourceDirectory);
        $uploadContext->getTemporaryImportDirectory()->willReturn($importDirectory);

        // create dummy files
        $filename1 = $importDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $importDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $importedFiles = $this->getImportedFiles($uploadContext);

        $importedFiles->shouldHaveCount(2);
    }

    protected function createUploadBaseDirectory()
    {
        if (null === $this->uploadDirectory) {
            $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR
                . 'pim_spec' . DIRECTORY_SEPARATOR . uniqid();
            mkdir($this->uploadDirectory, 0700, true);
        }
    }

    protected function removeUploadBaseDirectory()
    {
        $this->rrmdir($this->uploadDirectory);
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir") {
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * @return string
     */
    protected function createSourceDirectory()
    {
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_TMP;
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        return $directory;
    }

    /**
     * @return string
     */
    protected function createImportDirectory()
    {
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_IMPORTED;
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        return $directory;
    }
}
