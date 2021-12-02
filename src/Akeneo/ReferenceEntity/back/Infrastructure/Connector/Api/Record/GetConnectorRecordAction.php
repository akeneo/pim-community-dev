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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordByReferenceEntityAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\Hal\AddHalDownloadLinkToRecordImages;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorRecordAction
{
    public function __construct(
        private FindConnectorRecordByReferenceEntityAndCodeInterface $findConnectorRecord,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private AddHalDownloadLinkToRecordImages $addHalLinksToImageValues,
        private SecurityFacade $securityFacade    ) {
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(string $referenceEntityIdentifier, string $code): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();

        try {
            $recordCode = RecordCode::fromString($code);
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        if (!$this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $record = $this->findConnectorRecord->find($referenceEntityIdentifier, $recordCode);

        if (!$record instanceof ConnectorRecord) {
            throw new NotFoundHttpException(sprintf('Record "%s" does not exist for the reference entity "%s".', $recordCode, $referenceEntityIdentifier));
        }

        $normalizedRecord = $record->normalize();
        $normalizedRecord = current(($this->addHalLinksToImageValues)($referenceEntityIdentifier, [$normalizedRecord]));

        return new JsonResponse($normalizedRecord);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_record_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entity records.');
        }
    }
}
