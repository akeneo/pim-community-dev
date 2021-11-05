<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GetConnectorAttributeOptionAction
{
    /** @var FindConnectorAttributeOptionInterface */
    private $findConnectorAttributeOptionQuery;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery,
        ReferenceEntityExistsInterface $referenceEntityExists,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorAttributeOptionQuery = $findConnectorAttributeOptionQuery;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $attributeCode, string $optionCode): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (false === $referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        try {
            $attributeCode = AttributeCode::fromString($attributeCode);
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeOption = $this->findConnectorAttributeOptionQuery->find($referenceEntityIdentifier, $attributeCode, $optionCode);

        if (null === $attributeOption) {
            throw new NotFoundHttpException(sprintf('Attribute option "%s" does not exist for the attribute "%s".', $optionCode, $attributeCode));
        }

        $normalizedAttributeOption = $attributeOption->normalize();

        return new JsonResponse($normalizedAttributeOption);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        $acl = 'pim_api_reference_entity_list';

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
