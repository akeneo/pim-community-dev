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

    /** @var string */
    public $identifier;

    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var string|null */
    public $image;

    /** @var []|null */
    public $values;

    /** @var array */
    public $completeness;

    public function normalize(): array
    {
        $defaultCompleteness = ['complete' => 0, 'required' => 0];

        return [
            self::IDENTIFIER                  => $this->identifier,
            self::REFERENCE_ENTITY_IDENTIFIER => $this->referenceEntityIdentifier,
            self::CODE                        => $this->code,
            self::LABELS                      => $this->labels,
            self::IMAGE                       => $this->image,
            self::VALUES                      => $this->values,
            self::COMPLETENESS                => (null === $this->completeness) ? $defaultCompleteness : $this->completeness,
        ];
    }
}
