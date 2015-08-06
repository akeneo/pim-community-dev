<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploaderInterface;
use Prophecy\Argument;

class UploadCheckerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        UploaderInterface $uploader,
        AssetRepositoryInterface $assetRepo
    ) {
        $this->beConstructedWith($uploader, $assetRepo);
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

    function it_checks_a_valid_filename_for_non_existing_asset($uploader, $assetRepo)
    {
        $filename = 'foobar-fr_FR.png';

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);
        $uploader->getUserUploadDir()->willReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . 'source');
        $uploader->getUserScheduleDir()->willReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . 'scheduled');

        $assetRepo->findOneByIdentifier('foobar')->willReturn(null);

        $this->isValidFilename($filename)->shouldReturn(true);
    }

    function it_checks_an_invalid_filename_for_existing_asset_without_locale(
        $uploader,
        $assetRepo,
        AssetInterface $asset
    ) {
        $filename = 'foobar-fr_FR.png';

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);

        $assetRepo->findOneByIdentifier('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn([]);

        $this->isValidFilename($filename)->shouldReturn(false);
    }

    function it_checks_an_invalid_filename_for_existing_asset_with_other_locale(
        $uploader,
        $assetRepo,
        AssetInterface $asset,
        LocaleInterface $locale
    ) {
        $filename = 'foobar-fr_FR.png';

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);

        $assetRepo->findOneByIdentifier('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['en_US' => $locale]);

        $this->isValidFilename($filename)->shouldReturn(false);
    }

    function it_checks_a_valid_filename_for_existing_asset_with_locale(
        $uploader,
        $assetRepo,
        AssetInterface $asset,
        LocaleInterface $locale
    ) {
        $filename = 'foobar-fr_FR.png';

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);
        $uploader->getUserUploadDir()->willReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . 'source');
        $uploader->getUserScheduleDir()->willReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . 'scheduled');

        $assetRepo->findOneByIdentifier('foobar')->willReturn($asset);
        $asset->getLocales()->willReturn(['fr_FR' => $locale]);

        $this->isValidFilename($filename)->shouldReturn(true);
    }

    function it_checks_an_invalid_filename_for_existing_uploaded_file(
        $uploader,
        $assetRepo
    ) {
        $filename = 'foobar-fr_FR.png';

        $this->createUploadBaseDirectory();
        $sourceDirectory = $this->createSourceDirectory();

        file_put_contents($sourceDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);
        $uploader->getUserUploadDir()->willReturn($sourceDirectory);

        $assetRepo->findOneByIdentifier('foobar')->willReturn(null);

        $this->isValidFilename($filename)->shouldReturn(false);
    }

    function it_checks_an_invalid_filename_for_existing_scheduled_file(
        $uploader,
        $assetRepo
    ) {
        $filename = 'foobar-fr_FR.png';

        $this->createUploadBaseDirectory();
        $sourceDirectory = $this->createSourceDirectory();
        $scheduledDirectory = $this->createScheduledDirectory();

        file_put_contents($scheduledDirectory . DIRECTORY_SEPARATOR . $filename, 'foobar');

        $uploader->parseFilename($filename)->willReturn(['code' => 'foobar', 'locale' => 'fr_FR']);
        $uploader->getUserUploadDir()->willReturn($sourceDirectory);
        $uploader->getUserScheduleDir()->willReturn($scheduledDirectory);

        $assetRepo->findOneByIdentifier('foobar')->willReturn(null);

        $this->isValidFilename($filename)->shouldReturn(false);
    }

    protected function createUploadBaseDirectory()
    {
        if (null === $this->uploadDirectory) {
            $this->uploadDirectory = '/tmp/pim_spec/' . uniqid();
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
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
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
