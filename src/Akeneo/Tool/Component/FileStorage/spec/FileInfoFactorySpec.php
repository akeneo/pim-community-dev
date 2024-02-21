<?php

namespace spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfoFactorySpec extends ObjectBehavior
{
    function let(PathGeneratorInterface $pathGenerator)
    {
        $this->beConstructedWith($pathGenerator, FileInfo::class);
    }

    function it_creates_a_file_from_a_raw_file($pathGenerator)
    {
        $rawFile = new \SplFileInfo(__FILE__);

        $pathGenerator->generate($rawFile)->willReturn([
            'uuid'      => '12345',
            'file_name' => '12345_my_file.php',
            'path'      => '1/2/3/4/',
            'path_name' => '1/2/3/4/12345_my_file.php',
        ]);
        $file = $this->createFromRawFile($rawFile, 'destination');
        $file->shouldBeValidFile();
    }

    function it_creates_a_file_from_an_uploaded_file($pathGenerator)
    {
        $rawFile = new UploadedFile(__FILE__, 'FileInfoFactorySpec.php', 'text/x-php', filesize(__FILE__));

        $pathGenerator->generate($rawFile)->willReturn([
            'uuid'      => '12345',
            'file_name' => '12345_my_file.php',
            'path'      => '1/2/3/4/',
            'path_name' => '1/2/3/4/12345_my_file.php',
        ]);
        $file = $this->createFromRawFile($rawFile, 'destination');
        $file->shouldBeValidFile();
    }

    public function getMatchers(): array
    {
        return [
            'beValidFile' => function ($subject) {
                return
                    $subject->getKey() === '1/2/3/4/12345_my_file.php' &&
                    $subject->getOriginalFilename() === 'FileInfoFactorySpec.php' &&
                    $subject->getMimeType() === 'text/x-php' &&
                    $subject->getSize() === filesize(__FILE__) &&
                    $subject->getExtension() === 'php' &&
                    $subject->getStorage() === 'destination' &&
                    $subject->getHash() === sha1_file(__FILE__);
            }
        ];
    }
}
