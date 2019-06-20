<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Attributes details index action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAction
{
    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(
        FindAttributesDetailsInterface $findAttributesDetails,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->findAttributesDetails = $findAttributesDetails;
        $this->referenceEntityExists = $referenceEntityExists;
    }

    public function __invoke(string $referenceEntityIdentifier): JsonResponse
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($referenceEntityIdentifier);
        $attributesDetails = $this->findAttributesDetails->find($referenceEntityIdentifier);
        $normalizedAttributesDetails = $this->normalizeAttributesDetails($attributesDetails);

        return new JsonResponse($normalizedAttributesDetails);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException();
        }

        return $referenceEntityIdentifier;
    }

    /**
     * @param AttributeDetails[] $attributesDetails
     *
     * @return array
     */
    private function normalizeAttributesDetails(array $attributesDetails): array
    {
        return array_map(function (AttributeDetails $attributeDetails) {
            return $attributeDetails->normalize();
        }, $attributesDetails);
    }
}
