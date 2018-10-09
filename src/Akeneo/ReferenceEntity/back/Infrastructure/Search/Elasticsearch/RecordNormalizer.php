<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

/**
 * Generates a representation of a record for the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNormalizer implements RecordNormalizerInterface
{
    private const IDENTIFIER = 'identifier';
    private const REFERENCE_ENTITY_CODE = 'reference_entity_code';
    private const RECORD_LIST_SEARCH = 'record_list_search';

    /** @var RecordSearchMatrixNormalizer */
    private $searchMatrixGenerator;

    public function __construct(RecordSearchMatrixNormalizer $searchMatrixGenerator)
    {
        $this->searchMatrixGenerator = $searchMatrixGenerator;
    }

    public function normalize(Record $record): array
    {
        return [
            self::IDENTIFIER            => (string) $record->getIdentifier(),
            self::REFERENCE_ENTITY_CODE => (string) $record->getReferenceEntityIdentifier(),
            self::RECORD_LIST_SEARCH    => $this->searchMatrixGenerator->generate($record)
        ];
    }
}
