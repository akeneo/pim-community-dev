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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttributeOption;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeOptionAction
{
    private FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery;
    private ReferenceEntityExistsInterface $referenceEntityExists;
    private SecurityFacade $securityFacade;

    public function __construct(
        FindConnectorAttributeOptionInterface $findConnectorAttributeOptionQuery,
        ReferenceEntityExistsInterface $referenceEntityExists,
        SecurityFacade $securityFacade
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorAttributeOptionQuery = $findConnectorAttributeOptionQuery;
        $this->securityFacade = $securityFacade;
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

        if (!$referenceEntityExists) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        try {
            $attributeCode = AttributeCode::fromString($attributeCode);
            $optionCode = OptionCode::fromString($optionCode);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeOption = $this->findConnectorAttributeOptionQuery->find($referenceEntityIdentifier, $attributeCode, $optionCode);

        if (!$attributeOption instanceof ConnectorAttributeOption) {
            throw new NotFoundHttpException(sprintf('Attribute option "%s" does not exist for the attribute "%s".', $optionCode, $attributeCode));
        }

        $normalizedAttributeOption = $attributeOption->normalize();

        return new JsonResponse($normalizedAttributeOption);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}
