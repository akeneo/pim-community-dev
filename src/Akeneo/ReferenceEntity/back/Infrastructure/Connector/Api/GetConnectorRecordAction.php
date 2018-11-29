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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Hal\AddHalDownloadLinkToRecordImages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorRecordAction
{
    /** @var FindConnectorRecordByReferenceEntityAndCodeInterface */
    private $findConnectorRecord;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var AddHalDownloadLinkToRecordImages */
    private $addHalLinksToImageValues;

    public function __construct(
        FindConnectorRecordByReferenceEntityAndCodeInterface $findConnectorRecord,
        ReferenceEntityExistsInterface $referenceEntityExists,
        AddHalDownloadLinkToRecordImages $addHalLinksToImageValues
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->findConnectorRecord = $findConnectorRecord;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $code): JsonResponse
    {
        try {
            $recordCode = RecordCode::fromString($code);
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $record = ($this->findConnectorRecord)($referenceEntityIdentifier, $recordCode);

        if (null === $record) {
            throw new NotFoundHttpException(sprintf('Record "%s" does not exist for the reference entity "%s".', $recordCode, $referenceEntityIdentifier));
        }

        $normalizedRecord = $record->normalize();
        $normalizedRecord = current(($this->addHalLinksToImageValues)($referenceEntityIdentifier, [$normalizedRecord]));

        return new JsonResponse($normalizedRecord);
    }
}
