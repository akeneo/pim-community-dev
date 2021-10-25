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
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetConnectorAssetFamilyAttributesAction
{
    private FindConnectorAttributesByAssetFamilyIdentifierInterface $findConnectorAssetFamilyAttributes;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorAttributesByAssetFamilyIdentifierInterface $findConnectorAssetFamilyAttributes,
        AssetFamilyExistsInterface $assetFamilyExists,
        AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAssetFamilyAttributes = $findConnectorAssetFamilyAttributes;
        $this->addHalSelfLinkToNormalizedConnectorAttribute = $addHalSelfLinkToNormalizedConnectorAttribute;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier): JsonResponse
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

        $attributes = $this->findConnectorAssetFamilyAttributes->find($assetFamilyIdentifier);

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute->normalize();
            $normalizedAttribute = ($this->addHalSelfLinkToNormalizedConnectorAttribute)($assetFamilyIdentifier, $normalizedAttribute);
            $normalizedAttributes[] = $normalizedAttribute;
        }

        return new JsonResponse($normalizedAttributes);
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
