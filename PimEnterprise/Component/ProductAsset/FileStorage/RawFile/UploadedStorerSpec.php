<?php

namespace spec\PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\File;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedStorerSpec extends ObjectBehavior
{
    function let(PathGeneratorInterface $pathGenerator, MountManager $mountManager, SaverInterface $saver)
    {
        $this->beConstructedWith($pathGenerator, $mountManager, $saver);
    }

    function it_stores_an_uploaded_file($pathGenerator, $mountManager, $saver, CustomUploadedFile $file, Filesystem $fs)
    {
        touch('/tmp/x2f48h1');

        $file->getPathname()->willReturn('/tmp/x2f48h1');
        $file->getFilename()->willReturn('x2f48h1');
        $file->getMimeType()->willReturn('text/x-php');
        $file->getClientOriginalName()->willReturn('my file.php');
        $file->getClientSize()->willReturn(filesize(__FILE__));
        $file->getExtension()->willReturn('php');

        $mountManager->getFilesystem('destination')->willReturn($fs);

        $pathGenerator->generate($file)->willReturn(
            [
                'guid'      => '12345',
                'file_name' => '12345_my_file.php',
                'path'       => '1/2/3/4/',
                'file_path' => '1/2/3/4/12345_my_file.php',
            ]
        );

        $expected = new File();
        $expected->setSize(filesize(__FILE__));
        $expected->setOriginalFilename('my file.php');
        $expected->setFilename('12345_my_file.php');
        $expected->setGuid('12345');
        $expected->setPath('1/2/3/4/');
        $expected->setMimeType('text/x-php');
        $expected->setExtension('php');

        $fs->writeStream('1/2/3/4/12345_my_file.php', Argument::any())->shouldBeCalled();

        $saver->save(Argument::any())->shouldBeCalled();
        $this->store($file, 'destination')->shouldBeFileLike($expected);
    }

    public function getMatchers()
    {
        return [
            'beFileLike' => function ($subject, $expected) {
                return $subject->getSize() === $expected->getSize() &&
                    $subject->getOriginalFilename() === $expected->getOriginalFilename() &&
                    $subject->getFilename() === $expected->getFilename() &&
                    $subject->getGuid() === $expected->getGuid() &&
                    $subject->getPath() === $expected->getPath() &&
                    $subject->getMimeType() === $expected->getMimeType() &&
                    $subject->getExtension() === $subject->getExtension();
            },
        ];
    }
}

class CustomUploadedFile extends UploadedFile
{
    public function __construct()
    {
        parent::__construct(__FILE__, 'my file.php');
    }
}
