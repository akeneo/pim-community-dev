<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindSearchableRecordsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchableRecordItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetValuesReferenceEntitiesController
{
    public function __construct(
        private readonly FindSearchableRecordsInterface $findSearchableRecords,
    ) {
    }

    public function __invoke(Request $request, $referenceEntityIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $searchableRecords = $this->findSearchableRecords->byReferenceEntityIdentifier(ReferenceEntityIdentifier::fromString($referenceEntityIdentifier));

        $records = \array_map(function (SearchableRecordItem $record) {
                return [
                    'code' => $record->code,
                    'labels' => $record->labels,
                ];
            }, \iterator_to_array($searchableRecords));

        return new JsonResponse($records);
    }
}
