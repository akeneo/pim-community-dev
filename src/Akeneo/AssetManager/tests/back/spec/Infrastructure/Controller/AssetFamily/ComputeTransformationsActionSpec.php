<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Akeneo\AssetManager\Infrastructure\Controller\AssetFamily\ComputeTransformationsAction;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ComputeTransformationsActionSpec extends ObjectBehavior
{
    function let(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->beConstructedWith(
            $findOneAssetFamilyQuery,
            $jobLauncher,
            $tokenStorage,
            $jobInstanceRepository
        );
        $this->shouldHaveType(ComputeTransformationsAction::class);
    }

    function it_launches_a_job(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        AssetFamilyDetails $assetFamilyDetails,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ) {
        $findOneAssetFamilyQuery->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn($assetFamilyDetails);
        $jobInstanceRepository->findOneByIdentifier('asset_manager_compute_transformations')->willReturn($jobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobLauncher->launch($jobInstance, $user, [
            'asset_family_identifier' => 'packshot'
        ])->shouldBeCalledOnce();

        $this->__invoke('packshot')->shouldBeLike(
            new JsonResponse(null, Response::HTTP_NO_CONTENT)
        );
    }

    function it_throws_a_404_when_identifier_is_malformed()
    {
        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [' _&^ ']);
    }

    function it_throws_a_404_when_family_does_not_exist(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery
    ) {
        $findOneAssetFamilyQuery->find(AssetFamilyIdentifier::fromString('foo'))->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', ['foo']);
    }
}
