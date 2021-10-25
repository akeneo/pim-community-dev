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
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetConnectorAttributeOptionsAction
{
    private FindConnectorAttributeOptionsInterface $findConnectorAttributeOptionsQuery;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AttributeExistsInterface $attributeExists;

    private AttributeSupportsOptions $attributeSupportsOptions;

    public function __construct(
        FindConnectorAttributeOptionsInterface $findConnectorAttributeOptionsQuery,
        AssetFamilyExistsInterface $assetFamilyExists,
        AttributeExistsInterface $attributeExists,
        AttributeSupportsOptions $attributeSupportsOptions,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAttributeOptionsQuery = $findConnectorAttributeOptionsQuery;
        $this->attributeExists = $attributeExists;
        $this->attributeSupportsOptions = $attributeSupportsOptions;
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
        $acl = 'pim_api_asset_family_list';

        if (!$this->securityFacade->isGranted($acl)) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \LogicException('An user must be authenticated if ACLs are required');
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                throw new \LogicException(sprintf(
                    'An instance of "%s" is expected if ACLs are required',
                    UserInterface::class
                ));
            }

            $this->apiAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));
        }
    }
}
