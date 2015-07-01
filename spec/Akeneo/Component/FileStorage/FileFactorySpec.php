<?php

namespace spec\Akeneo\Component\FileStorage;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('\Akeneo\Component\FileStorage\Model\File');
    }

    function it_creates_a_file_from_a_raw_file()
    {
        $rawFile = new \SplFileInfo(__FILE__);

        $pathInfo = [
            'guid'      => '12345',
            'file_name' => '12345_my_file.php',
            'path'      => '1/2/3/4/',
            'path_name' => '1/2/3/4/12345_my_file.php',
        ];
        $file = $this->create($rawFile, $pathInfo, 'destination');
        $file->shouldBeValidFile();
    }

    function it_creates_a_file_from_an_uploaded_file()
    {
        $rawFile = new UploadedFile(__FILE__, 'FileFactorySpec.php', 'text/x-php', filesize(__FILE__));

        $pathInfo = [
            'guid'      => '12345',
            'file_name' => '12345_my_file.php',
            'path'      => '1/2/3/4/',
            'path_name' => '1/2/3/4/12345_my_file.php',
        ];
        $file = $this->create($rawFile, $pathInfo, 'destination');
        $file->shouldBeValidFile();
    }

    public function getMatchers()
    {
        return [
            'beValidFile' => function ($subject) {
                return
                    $subject->getGuid() === '12345' &&
                    $subject->getKey() === '1/2/3/4/12345_my_file.php' &&
                    $subject->getOriginalFilename() === 'FileFactorySpec.php' &&
                    $subject->getMimeType() === 'text/x-php' &&
                    $subject->getSize() === filesize(__FILE__) &&
                    $subject->getExtension() === 'php' &&
                    $subject->getStorage() === 'destination';
            }
        ];
    }
}
