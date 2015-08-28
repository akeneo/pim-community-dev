<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Prophecy\Argument;

class UploadContextSpec extends ObjectBehavior
{
    protected $uploadDirectory = '';

    function let()
    {
        $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pim_spec';

        $this->beConstructedWith($this->uploadDirectory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\UploadContext');
    }

    function it_must_be_initailized_with_username()
    {
        $this->shouldThrow('RuntimeException')
            ->during('getTemporaryUploadDirectory');

        $this->shouldThrow('RuntimeException')
            ->during('getTemporaryScheduleDirectory');
    }

    function it_gets_upload_directories()
    {
        $this->setUsername('foobar');

        $this->getTemporaryUploadDirectory()
            ->shouldReturn($this->uploadDirectory
                . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_TMP
                . DIRECTORY_SEPARATOR . 'foobar');
        $this->getTemporaryScheduleDirectory()
            ->shouldReturn($this->uploadDirectory
                . DIRECTORY_SEPARATOR . UploadContext::DIR_UPLOAD_SCHEDULED
                . DIRECTORY_SEPARATOR . 'foobar');
    }
}
