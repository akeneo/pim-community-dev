<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Upload\UploadContext;
use Prophecy\Argument;

class UploadContextSpec extends ObjectBehavior
{
    protected $uploadDirectory = '';

    function let()
    {
        $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pim_spec';
        $username              = 'foobar';

        $this->beConstructedWith($this->uploadDirectory, $username);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\UploadContext');
    }

    function it_must_be_initialized_with_username()
    {
        $this->shouldThrow('\RuntimeException')
            ->during('__construct', ['uploadDirectory', null]);

        $this->shouldThrow('\RuntimeException')
            ->during('__construct', ['uploadDirectory', '']);
    }

    function it_gets_upload_directories()
    {
        $this->getTemporaryUploadDirectory()
            ->shouldReturn($this->uploadDirectory
                . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_TMP
                . DIRECTORY_SEPARATOR . 'foobar');
        $this->getTemporaryImportDirectory()
            ->shouldReturn($this->uploadDirectory
                . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_IMPORTED
                . DIRECTORY_SEPARATOR . 'foobar');
    }
}
