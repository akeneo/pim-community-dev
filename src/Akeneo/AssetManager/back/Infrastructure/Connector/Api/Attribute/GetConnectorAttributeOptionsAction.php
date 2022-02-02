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
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeSupportsOptions;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeOptionsAction
{
    public function __construct(
        private FindConnectorAttributeOptionsInterface $findConnectorAttributeOptionsQuery,
        private AssetFamilyExistsInterface $assetFamilyExists,
        private AttributeExistsInterface $attributeExists,
        private AttributeSupportsOptions $attributeSupportsOptions,
        private SecurityFacadeInterface $securityFacade,
    ) {
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier, string $attributeCode): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

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
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeExists = $this->attributeExists->withAssetFamilyAndCode($assetFamilyIdentifier, $attributeCode);

        if (!$attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for asset family "%s".',
                (string) $attributeCode,
                (string) $assetFamilyIdentifier
            ));
        }

        $attributeSupportsOptions = $this->attributeSupportsOptions->supports($assetFamilyIdentifier, $attributeCode);

        if (!$attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $attributeOptions = $this->findConnectorAttributeOptionsQuery->find($assetFamilyIdentifier, $attributeCode);
        $normalizedAttributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $normalizedAttributeOptions[] = $attributeOption->normalize();
        }

        return new JsonResponse($normalizedAttributeOptions);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_asset_family_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list asset families.');
        }
    }
}
