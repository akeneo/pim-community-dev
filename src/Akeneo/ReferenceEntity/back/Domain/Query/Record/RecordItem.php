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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * Read model representing a record within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordItem
{
    private const IDENTIFIER = 'identifier';
    private const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const IMAGE = 'image';
    private const VALUES = 'values';
    private const COMPLETENESS = 'completeness';

    public string $identifier;
    public string $referenceEntityIdentifier;
    public string $code;
    public array $labels;
    public ?array $image = null;
    public ?array $values = null;
    public array $completeness;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => $this->identifier,
            self::REFERENCE_ENTITY_IDENTIFIER => $this->referenceEntityIdentifier,
            self::CODE => $this->code,
            self::LABELS => $this->labels,
            self::IMAGE => $this->image,
            self::VALUES => $this->values,
            self::COMPLETENESS => $this->completeness,
        ];
    }
}
