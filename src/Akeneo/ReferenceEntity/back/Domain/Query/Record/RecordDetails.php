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

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Read model representing a record's details.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordDetails
{
    private const IDENTIFIER = 'identifier';
    private const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const IMAGE = 'image';
    private const VALUES = 'values';
    private const PERMISSION = 'permission';
    private const EDIT_PERMISSION = 'edit';

    /** @var RecordIdentifier */
    public $identifier;

    /** @var ReferenceEntityIdentifier */
    public $referenceEntityIdentifier;

    /** @var RecordCode */
    public $code;

    /** @var LabelCollection */
    public $labels;

    /** @var Image */
    public $image;

    /** @var array */
    public $values;

    /** @var boolean */
    public $isAllowedToEdit = true;

    public function __construct(
        RecordIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code,
        LabelCollection $labels,
        Image $image,
        array $values,
        bool $isAllowedToEdit
    ) {
        $this->identifier = $identifier;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->code = $code;
        $this->labels = $labels;
        $this->values = $values;
        $this->image = $image;
        $this->isAllowedToEdit = $isAllowedToEdit;
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                  => $this->identifier->normalize(),
            self::REFERENCE_ENTITY_IDENTIFIER => $this->referenceEntityIdentifier->normalize(),
            self::CODE                        => $this->code->normalize(),
            self::LABELS                      => $this->labels->normalize(),
            self::IMAGE                       => $this->image->normalize(),
            self::VALUES                      => $this->values,
            self::PERMISSION                  => [
                self::EDIT_PERMISSION => $this->isAllowedToEdit,
            ],
        ];
    }
}
