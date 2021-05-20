<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeOptionAction
{
    private FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery;

    private AssetFamilyExistsInterface $assetFamilyExists;

    public function __construct(
        FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery,
        AssetFamilyExistsInterface $assetFamilyExists
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAttributeOptionQuery = $findConnectorAttributeOptionQuery;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier, string $attributeCode, string $optionCode): JsonResponse
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

        try {
            $attributeCode = AttributeCode::fromString($attributeCode);
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeOption = $this->findConnectorAttributeOptionQuery->find($assetFamilyIdentifier, $attributeCode, $optionCode);

        if (null === $attributeOption) {
            throw new NotFoundHttpException(sprintf('Attribute option "%s" does not exist for the attribute "%s".', $optionCode, $attributeCode));
        }

        $normalizedAttributeOption = $attributeOption->normalize();

        return new JsonResponse($normalizedAttributeOption);
    }
}
