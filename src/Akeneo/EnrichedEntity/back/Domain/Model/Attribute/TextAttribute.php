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

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'text';

    /** @var AttributeMaxLength */
    private $maxLength;

    protected function __construct(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeRequired $required,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxLength $maxLength
    ) {
        parent::__construct($identifier, $enrichedEntityIdentifier, $code, $labelCollection, $order, $required,
            $valuePerChannel, $valuePerLocale);

        $this->maxLength = $maxLength;
    }

    public static function create(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeRequired $required,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxLength $maxLength
    ): self {
        return new self(
            $identifier, $enrichedEntityIdentifier, $code, $labelCollection, $order, $required, $valuePerChannel,
            $valuePerLocale, $maxLength
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'max_length' => $this->maxLength->normalize()
            ]
        );
    }


    public function setIsRequired(AttributeRequired $required): self
    {
        return new self(
            $this->identifier,
            $this->enrichedEntityIdentifier,
            $this->code,
            $this->labelCollection,
            $this->order,
            $required,
            $this->valuePerChannel,
            $this->valuePerLocale,
            $this->maxLength
        );
    }

    public function setMaxLength(AttributeMaxLength $newMaxLength): self
    {
        return new self(
            $this->identifier,
            $this->enrichedEntityIdentifier,
            $this->code,
            $this->labelCollection,
            $this->order,
            $this->required,
            $this->valuePerChannel,
            $this->valuePerLocale,
            $newMaxLength
        );
    }

    public function updateLabels(LabelCollection $labelCollection): self
    {
        return new self(
            $this->identifier,
            $this->enrichedEntityIdentifier,
            $this->code,
            $labelCollection,
            $this->order,
            $this->required,
            $this->valuePerChannel,
            $this->valuePerLocale,
            $this->maxLength
        );
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
