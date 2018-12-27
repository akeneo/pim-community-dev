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
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeSupportsOptions;

class GetConnectorAttributeOptionsAction
{
    /** @var FindConnectorAttributeOptionsInterface */
    private $findConnectorAttributeOptionsQuery;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var AttributeSupportsOptions */
    private $attributeSupportsOptions;

    public function __construct(
        FindConnectorAttributeOptionsInterface $findConnectorAttributeOptionsQuery,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AttributeExistsInterface $attributeExists,
        AttributeSupportsOptions $attributeSupportsOptions
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorAttributeOptionsQuery = $findConnectorAttributeOptionsQuery;
        $this->attributeExists = $attributeExists;
        $this->attributeSupportsOptions = $attributeSupportsOptions;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $attributeCode): JsonResponse
    {
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
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        $attributeExists = $this->attributeExists->withReferenceEntityAndCode($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeExists) {
            throw new NotFoundHttpException(sprintf(
                'Attribute "%s" does not exist for reference entity "%s".',
                (string) $attributeCode,
                (string) $referenceEntityIdentifier
            ));
        }

        $attributeSupportsOptions = ($this->attributeSupportsOptions)($referenceEntityIdentifier, $attributeCode);

        if (false === $attributeSupportsOptions) {
            throw new NotFoundHttpException(sprintf('Attribute "%s" does not support options.', $attributeCode));
        }

        $attributeOptions = ($this->findConnectorAttributeOptionsQuery)($referenceEntityIdentifier, $attributeCode);
        $normalizedAttributeOptions = [];

        foreach ($attributeOptions as $attributeOption) {
            $normalizedAttributeOptions[] = $attributeOption->normalize();
        }

        return new JsonResponse($normalizedAttributeOptions);
    }
}
