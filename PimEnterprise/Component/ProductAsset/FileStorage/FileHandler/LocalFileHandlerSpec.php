<?php

namespace spec\PimEnterprise\Component\ProductAsset\FileStorage\FileHandler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\FileStorage\PathGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\File;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;
use Prophecy\Argument;

class LocalFileHandlerSpec extends ObjectBehavior
{
    function let(PathGeneratorInterface $pathGenerator, MountManager $mountManager, SaverInterface $saver)
    {
        $this->beConstructedWith($pathGenerator, $mountManager, $saver, 'src', 'dest');
    }

    function it_handles_a_local_file($pathGenerator, $mountManager, $saver, CustomFileInfo $file)
    {
        $file->getPathname()->willReturn(__FILE__);
        $file->getFilename()->willReturn('my file.php');

        $pathGenerator->generate($file)->willReturn(
            [
                'guid'      => '12345',
                'file_name' => '12345_my_file.php',
                'path'      => '1/2/3/4/',
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

        $mountManager->move('src://' . __FILE__, 'dest://1/2/3/4/12345_my_file.php')->shouldBeCalled();

        $saver->save(Argument::any())->shouldBeCalled();
        $this->handle($file)->shouldBeFileLike($expected);
    }

    public function getMatchers()
    {
        return [
            'beFileLike' => function ($subject, $expected) {
                return $subject instanceof FileInterface &&
                    $subject->getSize() === $expected->getSize() &&
                    $subject->getOriginalFilename() === $expected->getOriginalFilename() &&
                    $subject->getFilename() === $expected->getFilename() &&
                    $subject->getGuid() === $expected->getGuid() &&
                    $subject->getPath() === $expected->getPath() &&
                    $subject->getMimeType() === $expected->getMimeType();
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
