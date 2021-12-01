<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeAction
{
    public function __construct(
        private FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAttributeQuery,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private SecurityFacade $securityFacade
    ) {
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $code, string $referenceEntityIdentifier): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $attributeCode = AttributeCode::fromString($code);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $referenceEntityExists = $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier);

        if (!$referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $attribute = $this->findConnectorAttributeQuery->find($referenceEntityIdentifier, $attributeCode);

        if (!$attribute instanceof ConnectorAttribute) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not exist for the reference entity "%s".', $code, $referenceEntityIdentifier));
        }

        $normalizedAttribute = $attribute->normalize();

        return new JsonResponse($normalizedAttribute);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}
