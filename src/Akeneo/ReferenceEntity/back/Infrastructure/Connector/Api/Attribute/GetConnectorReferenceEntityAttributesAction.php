<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute;

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetConnectorReferenceEntityAttributesAction
{
    /** @var FindConnectorAttributesByReferenceEntityIdentifierInterface */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AddHalSelfLinkToNormalizedConnectorAttribute */
    private $addHalSelfLinkToNormalizedConnectorAttribute;

    public function __construct(
        FindConnectorAttributesByReferenceEntityIdentifierInterface $findConnectorReferenceEntityAttributes,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalSelfLinkToNormalizedConnectorAttribute $addHalSelfLinkToNormalizedConnectorAttribute
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorReferenceEntityAttributes = $findConnectorReferenceEntityAttributes;
        $this->addHalSelfLinkToNormalizedConnectorAttribute = $addHalSelfLinkToNormalizedConnectorAttribute;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier): JsonResponse
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

        $attributes = ($this->findConnectorReferenceEntityAttributes)($referenceEntityIdentifier);

        $normalizedAttributes = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute->normalize();
            $normalizedAttribute = ($this->addHalSelfLinkToNormalizedConnectorAttribute)($referenceEntityIdentifier, $normalizedAttribute);
            $normalizedAttributes[] = $normalizedAttribute;
        }

        return new JsonResponse($normalizedAttributes);
    }
}
