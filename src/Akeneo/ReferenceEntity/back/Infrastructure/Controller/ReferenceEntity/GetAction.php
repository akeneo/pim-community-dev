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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Get one Reference entity by its identifier
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class GetAction
{
    /** @var FindReferenceEntityDetailsInterface */
    private $findOneReferenceEntityQuery;

    public function __construct(FindReferenceEntityDetailsInterface $findOneReferenceEntityQuery)
    {
        $this->findOneReferenceEntityQuery = $findOneReferenceEntityQuery;
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifierOr404($identifier);
        $referenceEntityDetails = $this->findReferenceEntityDetailsOr404($referenceEntityIdentifier);

        return new JsonResponse($referenceEntityDetails->normalize());
    }

    private function getReferenceEntityIdentifierOr404(string $identifier): ReferenceEntityIdentifier
    {
        try {
            return ReferenceEntityIdentifier::fromString($identifier);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    private function findReferenceEntityDetailsOr404(ReferenceEntityIdentifier $identifier): ReferenceEntityDetails
    {
        $result = ($this->findOneReferenceEntityQuery)($identifier);
        if (null === $result) {
            throw new NotFoundHttpException();
        }

        return $result;
    }
}
