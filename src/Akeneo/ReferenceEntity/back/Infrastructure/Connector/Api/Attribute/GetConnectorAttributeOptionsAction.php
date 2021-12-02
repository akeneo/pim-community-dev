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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeSupportsOptions;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorAttributeOptionsAction
{
    public function __construct(
        private FindConnectorAttributeOptionsInterface $findConnectorAttributeOptionsQuery,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private AttributeExistsInterface $attributeExists,
        private AttributeSupportsOptions $attributeSupportsOptions,
        private SecurityFacade $securityFacade    ) {
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $attributeCode): JsonResponse
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
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (!$attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = $this->attributeSupportsOptions->supports($referenceEntityIdentifier, $attributeCode);

        if (!$attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $attributeOptions = $this->findConnectorAttributeOptionsQuery->find($referenceEntityIdentifier, $attributeCode);
        $normalizedAttributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $normalizedAttributeOptions[] = $attributeOption->normalize();
        }

        return new JsonResponse($normalizedAttributeOptions);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}
