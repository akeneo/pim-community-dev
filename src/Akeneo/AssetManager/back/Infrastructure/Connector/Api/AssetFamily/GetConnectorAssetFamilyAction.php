<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\Hal\AddHalDownloadLinkToAssetFamilyImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAssetFamilyAction
{
    private FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamily;

    private AddHalDownloadLinkToAssetFamilyImage $addHalLinksToAssetFamilyImage;

    public function __construct(
        FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamily,
        AddHalDownloadLinkToAssetFamilyImage $addHalLinksToImageValues
    ) {
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

        /** /!\ /!\ /!\ /!\
         * Crappy tricks to only remove the image of the asset family on the API side....
         * @todo : To remove if the functional decide to not have an image on the asset family
         * @todo : Check the PR https://github.com/akeneo/pim-enterprise-dev/pull/6651 for real fix
         */
        if (array_key_exists('image', $normalizedAssetFamily)) {
            unset($normalizedAssetFamily['image']);
        }

        $normalizedAssetFamily = ($this->addHalLinksToAssetFamilyImage)($normalizedAssetFamily);

        return new JsonResponse($normalizedAssetFamily);
    }
}
