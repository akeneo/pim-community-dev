<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetByAssetFamilyAndCodeInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\Hal\AddHalDownloadLinkToAssetImages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorAssetAction
{
    private FindConnectorAssetByAssetFamilyAndCodeInterface $findConnectorAsset;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AddHalDownloadLinkToAssetImages $addHalLinksToImageValues;

    public function __construct(
        FindConnectorAssetByAssetFamilyAndCodeInterface $findConnectorAsset,
        AssetFamilyExistsInterface $assetFamilyExists,
        AddHalDownloadLinkToAssetImages $addHalLinksToImageValues
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAsset = $findConnectorAsset;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier, string $code): JsonResponse
    {
        try {
            $assetCode = AssetCode::fromString($code);
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $asset = $this->findConnectorAsset->find($assetFamilyIdentifier, $assetCode);

        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist for the asset family "%s".', $assetCode, $assetFamilyIdentifier));
        }

        $normalizedAsset = $asset->normalize();
        $normalizedAsset = current(($this->addHalLinksToImageValues)($assetFamilyIdentifier, [$normalizedAsset]));

        return new JsonResponse($normalizedAsset);
    }
}
