<?php

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\Hal\AddHalDownloadLinkToAssetFamilyImage;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetConnectorAssetFamilyAction
{
    private FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamily;

    private AddHalDownloadLinkToAssetFamilyImage $addHalLinksToAssetFamilyImage;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamily,
        AddHalDownloadLinkToAssetFamilyImage $addHalLinksToImageValues,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->findConnectorAssetFamily = $findConnectorAssetFamily;
        $this->addHalLinksToAssetFamilyImage = $addHalLinksToImageValues;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

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

    private function denyAccessUnlessAclIsGranted(): void
    {
        $acl = 'pim_api_asset_family_list';

        if (!$this->securityFacade->isGranted($acl)) {
            /**
             * TODO CXP-922: throw instead of logging
             */
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
