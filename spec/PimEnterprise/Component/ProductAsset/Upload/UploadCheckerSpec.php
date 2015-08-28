<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploaderInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadMessages;
use Prophecy\Argument;

class UploadCheckerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        AssetRepositoryInterface $assetRepo
    ) {
        $this->beConstructedWith($assetRepo);
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
        $filename = 'foobar-fr_FR.png';
        $expected = ['code' => 'foobar', 'locale' => 'fr_FR'];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'foobar.png';
        $expected = ['code' => 'foobar', 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'foobar-notALocale.png';
        $expected = ['code' => null, 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'Invalid-code-fr_FR.png';
        $expected = ['code' => null, 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);
    }

    function it_checks_a_valid_filename_for_non_existing_asset($assetRepo)
    {
        $filename = 'foobar-fr_FR.png';

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->validateSchedule($filename, 'dummySourceDir', 'dummyScheduledDir');
    }

    function it_checks_an_invalid_filename_for_existing_asset_without_locale(
        $assetRepo,
        AssetInterface $asset
    ) {
        $filename = 'foobar-fr_FR.png';

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn([]);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException')
            ->during('validateSchedule', [$filename, 'dummySourceDir', 'dummyScheduledDir']);
    }

    function it_checks_an_invalid_filename_for_existing_asset_with_other_locale(
        $assetRepo,
        AssetInterface $asset,
        LocaleInterface $locale
    ) {
        $filename = 'foobar-fr_FR.png';

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['en_US' => $locale]);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException')
            ->during('validateSchedule', [$filename, 'dummySourceDir', 'dummyScheduledDir']);
    }

    function it_checks_a_valid_filename_for_existing_asset_with_locale(
        $assetRepo,
        AssetInterface $asset,
        LocaleInterface $locale
    ) {
        $filename = 'foobar-fr_FR.png';

        $assetRepo->findOneByCode('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['fr_FR' => $locale]);

        $this->validateSchedule($filename, 'dummySourceDir', 'dummyScheduledDir');
    }

    function it_checks_an_invalid_filename_for_existing_uploaded_file(
        $assetRepo
    ) {
        $filename = 'foobar-fr_FR.png';

        $this->createUploadBaseDirectory();
        $sourceDirectory = $this->createSourceDirectory();

        file_put_contents($sourceDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\DuplicateFileException')
            ->during('validateSchedule', [$filename, $sourceDirectory, 'dummyScheduledDir']);
    }

    function it_checks_an_invalid_filename_for_existing_scheduled_file(
        $assetRepo
    ) {
        $filename = 'foobar-fr_FR.png';

        $this->createUploadBaseDirectory();
        $sourceDirectory    = $this->createSourceDirectory();
        $scheduledDirectory = $this->createScheduledDirectory();

        file_put_contents($scheduledDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $assetRepo->findOneByCode('foobar')->willReturn(null);

        $this->shouldThrow('PimEnterprise\Component\ProductAsset\Upload\Exception\DuplicateFileException')
            ->during('validateSchedule', [$filename, $sourceDirectory, $scheduledDirectory]);
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
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . 'source';
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
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
