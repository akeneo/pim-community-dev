<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\Hal\AddHalSelfLinkToNormalizedConnectorAttribute;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAttributesAction
{
    public function __construct(
        private FindConnectorAttributesByReferenceEntityIdentifierInterface $findConnectorReferenceEntityAttributes,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute,
        private SecurityFacade $securityFacade
    ) {
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (!$referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $attributes = $this->findConnectorReferenceEntityAttributes->find($referenceEntityIdentifier);

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute->normalize();
            $normalizedAttribute = ($this->addHalSelfLinkToNormalizedConnectorAttribute)($referenceEntityIdentifier, $normalizedAttribute);
            $normalizedAttributes[] = $normalizedAttribute;
        }

        return new JsonResponse($normalizedAttributes);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}
