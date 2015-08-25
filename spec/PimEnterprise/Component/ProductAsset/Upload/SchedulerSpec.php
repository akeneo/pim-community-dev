<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use Prophecy\Argument;

class SchedulerSpec extends ObjectBehavior
{
    protected $uploadDirectory = null;

    function let(
        UploadCheckerInterface $uploadChecker,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->beConstructedWith($uploadChecker, $rawFileStorer);

        $this->createUploadBaseDirectory();

        $this->setSourceDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . 'source');
        $this->setScheduleDirectory($this->uploadDirectory . DIRECTORY_SEPARATOR . 'scheduled');
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

    function it_schedules_files_for_processing()
    {
        $sourceDirectory = $this->createSourceDirectory();

        // create dummy files
        $filename1 = $sourceDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $sourceDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $this->schedule()->shouldReturn([
            ['file' => 'bar.png', 'error' => null],
            ['file' => 'foo.png', 'error' => null],
        ]);
    }

    function it_gets_scheduled_files()
    {
        $scheduleDirectory = $this->createScheduledDirectory();

        // create dummy files
        $filename1 = $scheduleDirectory . DIRECTORY_SEPARATOR . 'foo.png';
        file_put_contents($filename1, 'foo');
        $filename2 = $scheduleDirectory . DIRECTORY_SEPARATOR . 'bar.png';
        file_put_contents($filename2, 'bar');

        $scheduledFiles = $this->getScheduledFiles();

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
