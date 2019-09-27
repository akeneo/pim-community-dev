<?php

namespace Specification\Akeneo\Asset\Component\Upload;

use Akeneo\Asset\Component\Upload\Exception\DuplicateFileException;
use Akeneo\Asset\Component\Upload\Exception\InvalidLocaleException;
use Akeneo\Asset\Component\Upload\UploadChecker;
use Akeneo\Asset\Component\Upload\UploadCheckerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Upload\ParsedFilenameInterface;

class UploadCheckerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        AssetRepositoryInterface $assetRepo,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr
    ) {
        $localeEn->getCode()->willReturn('en_US');
        $localeFr->getCode()->willReturn('fr_FR');
        $localeRepository->findAll()->willReturn([$localeEn, $localeFr]);

        $localeEn->isActivated()->willReturn(true);
        $localeFr->isActivated()->willReturn(true);

        $this->beConstructedWith($assetRepo, $localeRepository);
    }

    function letGo()
    {
        $this->removeUploadBaseDirectory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UploadChecker::class);
        $this->shouldImplement(UploadCheckerInterface::class);
    }

    function it_parses_filename()
    {
        $parsedFilename = $this->getParsedFilename('foobar.jpg');
        $parsedFilename->shouldHaveType(ParsedFilenameInterface::class);
    }

    function it_checks_a_valid_filename_for_non_existing_asset(
        ParsedFilenameInterface $parsedFilename,
        $assetRepo
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->validateFilenameFormat($parsedFilename, 'dummySourceDir', 'dummyImportDir');
    }

    function it_checks_an_invalid_filename_for_existing_asset_without_locale(
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        $assetRepo,
        $localeEn
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $localeEn->isActivated()->willReturn(true);

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn([]);

        $this->shouldThrow(InvalidLocaleException::class)
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyImportDir']);
    }

    function it_checks_an_invalid_filename_for_existing_asset_with_other_locale(
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        $assetRepo,
        $localeEn,
        $localeFr
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');

        $localeFr->isActivated()->willReturn(true);

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['en_US' => $localeEn]);

        $this->shouldThrow(InvalidLocaleException::class)
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyImportDir']);
    }

    function it_checks_an_invalid_filename_for_non_activated_locale(ParsedFilenameInterface $parsedFilename, $localeFr)
    {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');

        $localeFr->isActivated()->willReturn(false);

        $this->shouldThrow(InvalidLocaleException::class)
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyImportDir']);
    }

    function it_checks_an_invalid_filename_for_unknown_locale(ParsedFilenameInterface $parsedFilename)
    {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fo_FO');

        $this->shouldThrow(new \RuntimeException(sprintf('Locale code %s is unknown', 'fo_FO')))
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyImportDir']);
    }

    function it_checks_a_valid_filename_for_existing_asset_with_locale(
        ParsedFilenameInterface $parsedFilename,
        AssetInterface $asset,
        $assetRepo,
        $localeEn,
        $localeFr
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['en_US' => $localeEn, 'fr_FR' => $localeFr]);
        $localeEn->isActivated()->willReturn(true);
        $localeFr->isActivated()->willReturn(true);

        $this->validateFilenameFormat($parsedFilename, 'dummySourceDir', 'dummyImportDir');
    }

    function it_checks_an_invalid_filename_for_existing_uploaded_file(
        ParsedFilenameInterface $parsedFilename,
        $assetRepo
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');
        $parsedFilename->getCleanFilename()->willReturn('foobar-fr_FR.jpg');

        $this->createUploadBaseDirectory();
        $sourceDirectory = $this->createSourceDirectory();

        file_put_contents($sourceDirectory . DIRECTORY_SEPARATOR . 'foobar-fr_FR.jpg', 'foobar');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->shouldThrow(DuplicateFileException::class)
            ->during('validateUpload', [$parsedFilename, $sourceDirectory, 'dummyImportDir']);
    }

    function it_checks_an_invalid_filename_for_existing_imported_file(
        ParsedFilenameInterface $parsedFilename,
        $assetRepo
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');
        $parsedFilename->getCleanFilename()->willReturn('foobar-fr_FR.png');

        $this->createUploadBaseDirectory();
        $sourceDirectory = $this->createSourceDirectory();
        $importDirectory = $this->createImportDirectory();

        $filename = 'foobar-fr_FR.png';
        file_put_contents($importDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->shouldThrow(DuplicateFileException::class)
            ->during('validateUpload', [$parsedFilename, $sourceDirectory, $importDirectory]);
    }

    function it_throws_an_exception_if_a_file_already_exists_in_the_filesystem(
        AssetRepositoryInterface $assetRepository,
        LocaleRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider,
        FilesystemInterface $filesystem,
        ParsedFilenameInterface $parsedFilename
    ) {
        $this->beConstructedWith($assetRepository, $localeRepository, $filesystemProvider);

        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');
        $parsedFilename->getCleanFilename()->willReturn('foobar-fr_FR.png');

        $this->createUploadBaseDirectory();
        $importDirectory = $this->createImportDirectory();

        $filesystemProvider->getFilesystem('tmpAssetUpload')->willReturn($filesystem);
        $filesystem->has('mass_upload_tmp/julia/foobar-fr_FR.png')->willReturn(true);

        $this->shouldThrow(DuplicateFileException::class)
            ->during('validateUpload', [$parsedFilename, 'mass_upload_tmp/julia', $importDirectory]);
    }

    protected function createUploadBaseDirectory()
    {
        if (null === $this->uploadDirectory) {
            $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR
                . 'pim_spec' . DIRECTORY_SEPARATOR . uniqid();
            $created = mkdir($this->uploadDirectory, 0700, true);
            if (!$created) {
                throw new \RuntimeException('unable to create upload base directory ' . $this->uploadDirectory);
            }
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
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . 'source';
        if (!is_dir($directory)) {
            $created = mkdir($directory, 0700, true);
            if (!$created) {
                throw new \RuntimeException('unable to create source directory ' . $directory);
            }
        }

        return $directory;
    }

    /**
     * @return string
     */
    protected function createImportDirectory()
    {
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . 'imported';
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        return $directory;
    }
}
