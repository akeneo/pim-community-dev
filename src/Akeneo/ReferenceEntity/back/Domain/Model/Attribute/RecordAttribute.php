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
class RecordAttribute extends AbstractAttribute
{
    public const ATTRIBUTE_TYPE = 'record';

    /** @var ReferenceEntityIdentifier */
    private $recordType;

    protected function __construct(
        AttributeIdentifier $identifier,
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        ReferenceEntityIdentifier $recordType
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

        $this->recordType = $recordType;
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
        ReferenceEntityIdentifier $recordType
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
            $recordType
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'record_type' => $this->recordType->normalize()
            ]
        );
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }

    public function getRecordType(): ReferenceEntityIdentifier
    {
        return $this->recordType;
    }

    public function setRecordType(ReferenceEntityIdentifier $recordType): void
    {
        $this->recordType = $recordType;
    }
}
