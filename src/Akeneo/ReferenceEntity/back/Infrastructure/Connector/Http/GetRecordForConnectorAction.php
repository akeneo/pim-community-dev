<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordForConnectorByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetRecordForConnectorAction
{
    /** @var FindRecordForConnectorByReferenceEntityAndCodeInterface */
    private $findRecordForConnectorQuery;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(
        FindRecordForConnectorByReferenceEntityAndCodeInterface $findRecordForConnectorQuery,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findRecordForConnectorQuery = $findRecordForConnectorQuery;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $recordCode): JsonResponse
    {
        try {
            $recordCode = RecordCode::fromString($recordCode);
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $record = ($this->findRecordForConnectorQuery)($referenceEntityIdentifier, $recordCode);

        if (null === $record) {
            throw new NotFoundHttpException(sprintf('Record "%s" does not exist for the reference entity "%s".', $recordCode, $referenceEntityIdentifier));
        }

        $normalizedRecord = $record->normalize();

        return new JsonResponse($normalizedRecord);
    }
}
