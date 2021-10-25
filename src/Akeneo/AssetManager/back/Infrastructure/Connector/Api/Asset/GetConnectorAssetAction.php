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
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorAssetAction
{
    private FindConnectorAssetByAssetFamilyAndCodeInterface $findConnectorAsset;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private AddHalDownloadLinkToAssetImages $addHalLinksToImageValues;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorAssetByAssetFamilyAndCodeInterface $findConnectorAsset,
        AssetFamilyExistsInterface $assetFamilyExists,
        AddHalDownloadLinkToAssetImages $addHalLinksToImageValues,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->assetFamilyExists = $assetFamilyExists;
        $this->findConnectorAsset = $findConnectorAsset;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $assetFamilyIdentifier, string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

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

    private function denyAccessUnlessAclIsGranted(): void
    {
        $acl = 'pim_api_asset_list';

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
