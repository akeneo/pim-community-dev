<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\Uploader;
use Prophecy\Argument;

class UploaderSpec extends ObjectBehavior
{
    protected $uploadDirectory = '/tmp/pim_spec';

    function let(
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->beConstructedWith($rawFileStorer, $this->uploadDirectory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\Uploader');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\UploaderInterface');
    }

    function it_parses_filename()
    {
        $filename = 'foobar-fr_FR.png';
        $expected = ['code' => 'foobar', 'locale' => 'fr_FR'];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'foobar.png';
        $expected = ['code' => 'foobar', 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'foobar-notALocale.png';
        $expected = ['code' => null, 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);

        $filename = 'Invalid-code-fr_FR.png';
        $expected = ['code' => null, 'locale' => null];

        $this->parseFilename($filename)->shouldReturn($expected);
    }

    function it_gets_upload_directories()
    {
        $this->setSubDirectory('foo');

        $this->getUserUploadDir()
            ->shouldReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . Uploader::DIR_UPLOAD_TMP . '/foo');
        $this->getUserScheduleDir()
            ->shouldReturn($this->uploadDirectory . DIRECTORY_SEPARATOR . Uploader::DIR_UPLOAD_SCHEDULED . '/foo');
    }
}
