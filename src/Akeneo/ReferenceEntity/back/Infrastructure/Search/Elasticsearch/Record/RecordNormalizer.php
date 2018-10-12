<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

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
    private const CODE = 'code';
    private const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    private const RECORD_LIST_SEARCH = 'record_list_search';

    /** @var RecordSearchMatrixNormalizer */
    private $recordSearchMatrixGenerator;

    public function __construct(RecordSearchMatrixNormalizer $searchMatrixGenerator)
    {
        $this->recordSearchMatrixGenerator = $searchMatrixGenerator;
    }

    public function normalize(Record $record): array
    {
        return [
            self::IDENTIFIER            => (string) $record->getIdentifier(),
            self::CODE                  => (string) $record->getCode(),
            self::REFERENCE_ENTITY_IDENTIFIER => (string) $record->getReferenceEntityIdentifier(),
            self::RECORD_LIST_SEARCH    => $this->recordSearchMatrixGenerator->generate($record)
        ];
    }
}
