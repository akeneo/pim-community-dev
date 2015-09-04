<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Prophecy\Argument;

class SchedulerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        UploadCheckerInterface $uploadChecker,
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith($uploadChecker, $fileStorer);

        $this->createUploadBaseDirectory();
    }

    function letGo()
    {
        $this->removeUploadBaseDirectory();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\Scheduler');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\SchedulerInterface');
    }

    function it_schedules_files_for_processing(UploadContext $uploadContext)
    {
        $sourceDirectory = $this->createSourceDirectory();
        $scheduledDirectory = $this->createScheduledDirectory();

        $uploadContext->getTemporaryUploadDirectory()->willReturn($sourceDirectory);
        $uploadContext->getTemporaryScheduleDirectory()->willReturn($scheduledDirectory);

        // create dummy files
        $filename1 = $sourceDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $sourceDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $this->schedule($uploadContext)
            ->shouldReturn([
            ['file' => 'bar.png', 'error' => null],
            ['file' => 'foo.png', 'error' => null],
        ]);
    }

    function it_gets_scheduled_files(UploadContext $uploadContext)
    {
        $sourceDirectory = $this->createSourceDirectory();
        $scheduledDirectory = $this->createScheduledDirectory();

        $uploadContext->getTemporaryUploadDirectory()->willReturn($sourceDirectory);
        $uploadContext->getTemporaryScheduleDirectory()->willReturn($scheduledDirectory);

        // create dummy files
        $filename1 = $scheduledDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $scheduledDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $scheduledFiles = $this->getScheduledFiles($uploadContext);

        $scheduledFiles->shouldHaveCount(2);
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
    protected function createScheduledDirectory()
    {
        $directory = $this->uploadDirectory . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_SCHEDULED;
        if (!is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        return $directory;
    }
}
