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

namespace Akeneo\EnrichedEntity\Domain\Query\Record;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

/**
 * Read model representing a record's details.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordDetails
{
    private const IDENTIFIER = 'identifier';
    private const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const VALUES = 'values';

    /** @var RecordIdentifier */
    private $identifier;

    /** @var EnrichedEntityIdentifier */
    private $enrichedEntityIdentifier;

    /** @var RecordCode */
    private $code;

    /** @var LabelCollection */
    private $labels;

    /** @var array */
    private $values;

    public function __construct(
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        RecordCode $code,
        LabelCollection $labels,
        array $values
    ) {
        $this->identifier = $identifier;
        $this->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $this->code = $code;
        $this->labels = $labels;
        $this->values = $values;
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                 => $this->identifier->normalize(),
            self::ENRICHED_ENTITY_IDENTIFIER => $this->enrichedEntityIdentifier->normalize(),
            self::CODE                       => $this->code->normalize(),
            self::LABELS                     => $this->labels->normalize(),
            self::VALUES                     => $this->values,
        ];
    }
}
