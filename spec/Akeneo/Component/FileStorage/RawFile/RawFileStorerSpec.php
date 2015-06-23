<?php

namespace spec\Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\FileFactoryInterface;
use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\PathGeneratorInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class RawFileStorerSpec extends ObjectBehavior
{
    function let(
        PathGeneratorInterface $pathGenerator,
        MountManager $mountManager,
        SaverInterface $saver,
        FileFactoryInterface $factory
    ) {
        $this->beConstructedWith($pathGenerator, $mountManager, $saver, $factory);
    }

    function it_stores_a_raw_file(
        $pathGenerator,
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInterface $file
    ) {
        $localPathname = __DIR__ . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile->getPathname()->willReturn($localPathname);
        $fs->has(Argument::any())->willReturn(false);

        $mountManager->getFilesystem('destination')->willReturn($fs);
        $pathGenerator->generate($rawFile)->willReturn(['path_infos']);
        $factory->create($rawFile, ['path_infos'], 'destination')->willReturn($file);

        $fs->writeStream(Argument::any(), Argument::any())->shouldBeCalled();

        $saver->save($file, ['flush_only_object' => true])->shouldBeCalled();
        $this->store($rawFile, 'destination');
    }

    function it_throws_an_exception_if_the_file_can_not_be_writen_on_the_filesystem(
        $pathGenerator,
        $mountManager,
        $factory,
        $saver,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInterface $file
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $pathGenerator->generate($rawFile)->willReturn(['path_infos']);
        $factory->create($rawFile, ['path_infos'], 'destination')->willReturn($file);
        $fs->writeStream(Argument::any(), Argument::any())->willReturn(false);

        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
    }

    function it_throws_an_exception_if_the_file_already_exists_on_the_filesystem(
        $pathGenerator,
        $mountManager,
        $factory,
        \SplFileInfo $rawFile,
        Filesystem $fs,
        FileInterface $file
    ) {
        $rawFile->getPathname()->willReturn(__FILE__);
        $fs->has(Argument::any())->willReturn(true);
        $fs->writeStream(Argument::any(), Argument::any())->willThrow(new FileExistsException('The file exists.'));
        $mountManager->getFilesystem('destination')->willReturn($fs);
        $pathGenerator->generate($rawFile)->willReturn(['path_infos']);
        $factory->create($rawFile, ['path_infos'], 'destination')->willReturn($file);
        $file->getKey()->willReturn('key-file');

        $this->shouldThrow(
            new FileTransferException(
                sprintf('Unable to move the file "%s" to the "destination" filesystem.', __FILE__)
            )
        )->during('store', [$rawFile, 'destination']);
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
