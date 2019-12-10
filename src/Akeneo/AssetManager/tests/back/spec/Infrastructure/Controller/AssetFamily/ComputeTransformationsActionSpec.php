<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationFromAssetFamilyIdentifierLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Akeneo\AssetManager\Infrastructure\Controller\AssetFamily\ComputeTransformationsAction;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComputeTransformationsActionSpec extends ObjectBehavior
{
    function let(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationsLauncher
    ) {
        $this->beConstructedWith(
            $findOneAssetFamilyQuery,
            $computeTransformationsLauncher
        );
        $this->shouldHaveType(ComputeTransformationsAction::class);
    }

    function it_launches_a_job(
        FindAssetFamilyDetailsInterface $findOneAssetFamilyQuery,
        ComputeTransformationFromAssetFamilyIdentifierLauncherInterface $computeTransformationsLauncher,
        AssetFamilyDetails $assetFamilyDetails
    ) {
        $findOneAssetFamilyQuery->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn($assetFamilyDetails);
        $computeTransformationsLauncher->launch(AssetFamilyIdentifier::fromString('packshot'))->shouldBeCalledOnce();

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
