<?php

namespace spec\PimEnterprise\Component\ProductAsset\FileStorage;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileFactorySpec extends ObjectBehavior
{
    function it_create_a_file_from_a_raw_file()
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

    function it_create_a_file_from_an_uploaded_file()
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
                    $subject->getFilename() === '12345_my_file.php' &&
                    $subject->getPathname() === '1/2/3/4/12345_my_file.php' &&
                    $subject->getPath() ===  '1/2/3/4/' &&
                    $subject->getOriginalFilename() === 'FileFactorySpec.php' &&
                    $subject->getMimeType() === 'text/x-php' &&
                    $subject->getSize() === filesize(__FILE__) &&
                    $subject->getExtension() === 'php' &&
                    $subject->getStorage() === 'destination';
            }
        ];
    }
}
