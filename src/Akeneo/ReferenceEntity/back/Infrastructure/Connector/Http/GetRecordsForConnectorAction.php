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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordsForConnectorByReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetRecordsForConnectorAction
{
    /** @var FindRecordsForConnectorByReferenceEntityInterface */
    private $findRecordsForConnector;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var int */
    private $limit;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        FindRecordsForConnectorByReferenceEntityInterface $findRecordsForConnector,
        int $limit
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findRecordsForConnector = $findRecordsForConnector;

        Assert::greaterThan($limit, 0);
        $this->limit = $limit;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterCode = null !== $searchAfter ? RecordCode::fromString($searchAfter) : null;
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $records = ($this->findRecordsForConnector)($referenceEntityIdentifier, $searchAfterCode, $this->limit);
        $records = array_map(function (RecordForConnector $record) {
            return $record->normalize();
        }, $records);

        return new JsonResponse([
            '_embedded' => [
                '_items' => $records,
            ]
        ]);
    }
}
