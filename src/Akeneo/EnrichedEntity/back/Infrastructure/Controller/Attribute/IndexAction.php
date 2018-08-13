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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityExistsInterface;
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

    /** @var EnrichedEntityExistsInterface */
    private $enrichedEntityExists;

    public function __construct(
        FindAttributesDetailsInterface $findAttributesDetails,
        EnrichedEntityExistsInterface $enrichedEntityExists
    ) {
        $this->findAttributesDetails = $findAttributesDetails;
        $this->enrichedEntityExists = $enrichedEntityExists;
    }

    public function __invoke(string $enrichedEntityIdentifier): JsonResponse
    {
        $enrichedEntityIdentifier = $this->getEnrichedEntityIdentifierOr404($enrichedEntityIdentifier);
        $attributesDetails = ($this->findAttributesDetails)($enrichedEntityIdentifier);
        $normalizedAttributesDetails = $this->normalizeAttributesDetails($attributesDetails);

        return new JsonResponse($normalizedAttributesDetails);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getEnrichedEntityIdentifierOr404(string $identifier): EnrichedEntityIdentifier
    {
        try {
            $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if (!$this->enrichedEntityExists->withIdentifier($enrichedEntityIdentifier)) {
            throw new NotFoundHttpException();
        }

        return $enrichedEntityIdentifier;
    }

    /**
     * @param AbstractAttributeDetails[] $attributesDetails
     *
     * @return array
     */
    private function normalizeAttributesDetails(array $attributesDetails): array
    {
        return array_map(function (AbstractAttributeDetails $attributeDetails) {
            return $attributeDetails->normalize();
        }, $attributesDetails);
    }
}
