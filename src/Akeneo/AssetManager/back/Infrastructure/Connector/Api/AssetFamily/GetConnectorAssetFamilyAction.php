<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\Hal\AddHalDownloadLinkToAssetFamilyImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAssetFamilyAction
{
    /** @var FindConnectorAssetFamilyByAssetFamilyIdentifierInterface */
    private $findConnectorAssetFamily;

    /** @var AssetFamilyExistsInterface */
    private $assetFamilyExists;

    /** @var AddHalDownloadLinkToAssetFamilyImage */
    private $addHalLinksToAssetFamilyImage;

    public function __construct(
        FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamily,
        AssetFamilyExistsInterface $assetFamilyExists,
        AddHalDownloadLinkToAssetFamilyImage $addHalLinksToImageValues
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAssetFamily = $findConnectorAssetFamily;
        $this->addHalLinksToAssetFamilyImage = $addHalLinksToImageValues;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code): JsonResponse
    {
        try {
            $code = AssetFamilyIdentifier::fromString($code);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $assetFamily = $this->findConnectorAssetFamily->find($code);

        if (null === $assetFamily) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $code));
        }

        $normalizedAssetFamily = $assetFamily->normalize();
        $normalizedAssetFamily = ($this->addHalLinksToAssetFamilyImage)($normalizedAssetFamily);

        return new JsonResponse($normalizedAssetFamily);
    }
}
