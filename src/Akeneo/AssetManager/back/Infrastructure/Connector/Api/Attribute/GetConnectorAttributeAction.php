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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeAction
{
    private FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttributeQuery;

    private AssetFamilyExistsInterface $assetFamilyExists;

    public function __construct(
        FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttributeQuery,
        AssetFamilyExistsInterface $assetFamilyExists
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAttributeQuery = $findConnectorAttributeQuery;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code, string $assetFamilyIdentifier): JsonResponse
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

        $attributeCode = AttributeCode::fromString($code);
        $attribute = $this->findConnectorAttributeQuery->find($assetFamilyIdentifier, $attributeCode);

        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist for the asset family "%s".', $code, $assetFamilyIdentifier));
        }

        $normalizedAttribute = $attribute->normalize();

        return new JsonResponse($normalizedAttribute);
    }
}
