<?php

namespace spec\PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\File;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class LocalStorerSpec extends ObjectBehavior
{
    function let(PathGeneratorInterface $pathGenerator, MountManager $mountManager, SaverInterface $saver)
    {
        $this->beConstructedWith($pathGenerator, $mountManager, $saver);
    }

    function it_stores_a_local_file($pathGenerator, $mountManager, $saver, CustomFileInfo $file, Filesystem $fs)
    {
        $localPathname = __DIR__ . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);

        $file->getPathname()->willReturn($localPathname);
        $file->getFilename()->willReturn('my file.php');
        $file->getExtension()->willReturn('php');

        $mountManager->getFilesystem('destination')->willReturn($fs);

        $pathGenerator->generate($file)->willReturn(
            [
                'guid'      => '12345',
                'file_name' => '12345_my_file.php',
                'path'      => '1/2/3/4/',
                'file_path' => '1/2/3/4/12345_my_file.php',
            ]
        );

        $expected = new File();
        $expected->setSize(0);
        $expected->setOriginalFilename('my file.php');
        $expected->setFilename('12345_my_file.php');
        $expected->setGuid('12345');
        $expected->setPath('1/2/3/4/');
        $expected->setMimeType('inode/x-empty');
        $expected->setExtension('php');

        $fs->writeStream('1/2/3/4/12345_my_file.php', Argument::any())->shouldBeCalled();

        $saver->save(Argument::any())->shouldBeCalled();
        $this->store($file, 'destination')->shouldBeFileLike($expected);
    }

    public function getMatchers()
    {
        return [
            'beFileLike' => function ($subject, $expected) {
                return $subject instanceof FileInterface &&
                $subject->getOriginalFilename() === $expected->getOriginalFilename() &&
                $subject->getFilename() === $expected->getFilename() &&
                $subject->getGuid() === $expected->getGuid() &&
                $subject->getPath() === $expected->getPath() &&
                $subject->getMimeType() === $expected->getMimeType() &&
                $subject->getSize() === $subject->getSize() &&
                $subject->getExtension() === $subject->getExtension();
            },
        ];
    }
}

class CustomFileInfo extends \SplFileInfo
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }
}
