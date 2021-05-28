<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributesByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute\Hal\AddHalSelfLinkToNormalizedConnectorAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAssetFamilyAttributesAction
{
    private FindConnectorAttributesByAssetFamilyIdentifierInterface $findConnectorAssetFamilyAttributes;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute;

    public function __construct(
        FindConnectorAttributesByAssetFamilyIdentifierInterface $findConnectorAssetFamilyAttributes,
        AssetFamilyExistsInterface $assetFamilyExists,
        AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAssetFamilyAttributes = $findConnectorAssetFamilyAttributes;
        $this->addHalSelfLinkToNormalizedConnectorAttribute = $addHalSelfLinkToNormalizedConnectorAttribute;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier): JsonResponse
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $assetFamilyExists = $this->assetFamilyExists->withIdentifier($assetFamilyIdentifier);

        if (!$assetFamilyExists) {
            throw new NotFoundHttpException(sprintf('Asset family "%s" does not exist.', $assetFamilyIdentifier));
        }

        $attributes = $this->findConnectorAssetFamilyAttributes->find($assetFamilyIdentifier);

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute->normalize();
            $normalizedAttribute = ($this->addHalSelfLinkToNormalizedConnectorAttribute)($assetFamilyIdentifier, $normalizedAttribute);
            $normalizedAttributes[] = $normalizedAttribute;
        }

        return new JsonResponse($normalizedAttributes);
    }
}
