<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Upload\ParsedFilenameInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Prophecy\Argument;

class ImporterSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        UploadCheckerInterface $uploadChecker,
        ParsedFilenameInterface $fooParsed,
        ParsedFilenameInterface $barParsed,
        FileStorerInterface $fileStorer
    ) {
        $fooParsed->getAssetCode()->willReturn('foo');
        $fooParsed->getLocaleCode()->willReturn(null);
        $barParsed->getAssetCode()->willReturn('bar');
        $barParsed->getLocaleCode()->willReturn(null);

        $uploadChecker->getParsedFilename('foo.png')->willReturn($fooParsed);
        $uploadChecker->getParsedFilename('bar.png')->willReturn($barParsed);

        $this->beConstructedWith($uploadChecker, $fileStorer);

        $this->createUploadBaseDirectory();
    }

    function letGo()
    {
        $this->removeUploadBaseDirectory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\Importer');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\ImporterInterface');
    }

    function it_imports_files_for_processing(UploadContext $uploadContext)
    {
        $sourceDirectory = $this->createSourceDirectory();
        $importDirectory = $this->createImportDirectory();

        $uploadContext->getTemporaryUploadDirectory()->willReturn($sourceDirectory);
        $uploadContext->getTemporaryImportDirectory()->willReturn($importDirectory);

        // create dummy files
        $filename1 = $sourceDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $sourceDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $this->import($uploadContext)
            ->shouldReturn([
                ['file' => 'bar.png', 'error' => null],
                ['file' => 'foo.png', 'error' => null],
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
