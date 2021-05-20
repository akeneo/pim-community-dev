<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Attributes details index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    private FindAttributesDetailsInterface $findAttributesDetails;

    private AssetFamilyExistsInterface $assetFamilyExists;

    public function __construct(
        FindAttributesDetailsInterface $findAttributesDetails,
        AssetFamilyExistsInterface $assetFamilyExists
    ) {
        $this->findAttributesDetails = $findAttributesDetails;
        $this->assetFamilyExists = $assetFamilyExists;
    }

    public function __invoke(string $assetFamilyIdentifier): JsonResponse
    {
        $assetFamilyIdentifier = $this->getAssetFamilyIdentifierOr404($assetFamilyIdentifier);
        $attributesDetails = $this->findAttributesDetails->find($assetFamilyIdentifier);
        $normalizedAttributesDetails = $this->normalizeAttributesDetails($attributesDetails);

        return new JsonResponse($normalizedAttributesDetails);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getAssetFamilyIdentifierOr404(string $identifier): AssetFamilyIdentifier
    {
        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->assetFamilyExists->withIdentifier($assetFamilyIdentifier)) {
            throw new NotFoundHttpException();
        }

        return $assetFamilyIdentifier;
    }

    /**
     * @param AttributeDetails[] $attributesDetails
     *
     * @return array
     */
    private function normalizeAttributesDetails(array $attributesDetails): array
    {
        return array_map(fn(AttributeDetails $attributeDetails) => $attributeDetails->normalize(), $attributesDetails);
    }
}
