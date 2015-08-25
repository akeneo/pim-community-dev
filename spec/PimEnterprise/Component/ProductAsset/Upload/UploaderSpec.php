<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\Uploader;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UploaderSpec extends ObjectBehavior
{
    protected $uploadDirectory = '';

    function let(
        TokenStorageInterface $tokenStorage,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pim_spec';

        $this->beConstructedWith($tokenStorage, $rawFileStorer, $this->uploadDirectory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\Uploader');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\UploaderInterface');
    }

    function it_gets_upload_directories(
        TokenInterface $token,
        UserInterface $user,
        $tokenStorage
    ) {
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('foo');
        $tokenStorage->getToken()->willReturn($token);

        $this->getUserUploadDir()
            ->shouldReturn($this->uploadDirectory . DIRECTORY_SEPARATOR
                . Uploader::DIR_UPLOAD_TMP . DIRECTORY_SEPARATOR . 'foo');
        $this->getUserScheduleDir()
            ->shouldReturn($this->uploadDirectory . DIRECTORY_SEPARATOR
                . Uploader::DIR_UPLOAD_SCHEDULED . DIRECTORY_SEPARATOR . 'foo');
    }
}
