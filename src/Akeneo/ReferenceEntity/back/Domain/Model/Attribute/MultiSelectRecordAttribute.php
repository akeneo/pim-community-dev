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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class MultiSelectRecordAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'multi_select_record';

    /** @var AttributeReferenceEntityIdentifier */
    private $referenceEntityIdentifierRecordType;

    protected function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeReferenceEntityIdentifier $referenceEntityIdentifierRecordType
    ) {
        parent::__construct(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->referenceEntityIdentifierRecordType = $referenceEntityIdentifierRecordType;
    }

    public static function create(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeReferenceEntityIdentifier $referenceEntityIdentifierRecordType
    ): self {
        return new self(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $referenceEntityIdentifierRecordType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'reference_entity_identifier_record_type' => $this->referenceEntityIdentifierRecordType->normalize()
            ]
        );
    }

    public function getReferenceEntityIdentifierRecordType(): AttributeReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifierRecordType;
    }

    public function setReferenceEntityIdentifierRecordType(AttributeReferenceEntityIdentifier $referenceEntityIdentifierRecordType): void
    {
        $this->referenceEntityIdentifierRecordType = $referenceEntityIdentifierRecordType;
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
