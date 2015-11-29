<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\ParsedFilenameInterface;
use Prophecy\Argument;

class UploadCheckerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        AssetRepositoryInterface $assetRepo,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr,
        LocaleRepositoryInterface $localeRepository
    ) {
        $localeEn->getCode()->willReturn('en_US');
        $localeFr->getCode()->willReturn('fr_FR');
        $localeRepository->findAll()->willReturn([$localeEn, $localeFr]);

        $localeEn->isActivated()->willReturn(true);
        $localeFr->isActivated()->willReturn(true);

        $this->beConstructedWith($assetRepo, $localeRepository, $localeRepository);
    }

    function letGo()
    {
        $this->removeUploadBaseDirectory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\UploadChecker');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface');
    }

    function it_parses_filename()
    {
        $parsedFilename = $this->getParsedFilename('foobar.jpg');
        $parsedFilename->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\ParsedFilenameInterface');
    }

    function it_checks_a_valid_filename_for_non_existing_asset(
        ParsedFilenameInterface $parsedFilename,
        $assetRepo
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('en_US');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->validateFilenameFormat($parsedFilename, 'dummySourceDir', 'dummyScheduledDir');
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

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException')
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyScheduledDir']);
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

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException')
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyScheduledDir']);
    }

    function it_checks_an_invalid_filename_for_non_activated_locale(ParsedFilenameInterface $parsedFilename, $localeFr)
    {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');

        $localeFr->isActivated()->willReturn(false);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException')
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyScheduledDir']);
    }

    function it_checks_an_invalid_filename_for_unknown_locale(ParsedFilenameInterface $parsedFilename)
    {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fo_FO');

        $this->shouldThrow(new \RuntimeException(sprintf('Locale code %s is unknown', 'fo_FO')))
            ->during('validateFilenameFormat', [$parsedFilename, 'dummySourceDir', 'dummyScheduledDir']);
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

        $this->validateFilenameFormat($parsedFilename, 'dummySourceDir', 'dummyScheduledDir');
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

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\DuplicateFileException')
            ->during('validateUpload', [$parsedFilename, $sourceDirectory, 'dummyScheduledDir']);
    }

    function it_checks_an_invalid_filename_for_existing_scheduled_file(
        ParsedFilenameInterface $parsedFilename,
        $assetRepo
    ) {
        $parsedFilename->getAssetCode()->willReturn('foobar');
        $parsedFilename->getLocaleCode()->willReturn('fr_FR');
        $parsedFilename->getCleanFilename()->willReturn('foobar-fr_FR.png');

        $this->createUploadBaseDirectory();
        $sourceDirectory    = $this->createSourceDirectory();
        $scheduledDirectory = $this->createScheduledDirectory();

        $filename = 'foobar-fr_FR.png';
        file_put_contents($scheduledDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\DuplicateFileException')
            ->during('validateUpload', [$parsedFilename, $sourceDirectory, $scheduledDirectory]);
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
    protected function createScheduledDirectory()
    {
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . 'scheduled';
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        return $directory;
    }
}
