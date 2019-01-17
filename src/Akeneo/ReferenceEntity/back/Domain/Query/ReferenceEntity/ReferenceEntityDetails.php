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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;

/**
 * Read model representing a reference entity detailled for display purpose (like a form)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityDetails
{
    public const IDENTIFIER = 'identifier';
    public const LABELS = 'labels';
    public const IMAGE = 'image';
    public const RECORD_COUNT = 'record_count';
    public const ATTRIBUTES = 'attributes';
    public const PERMISSION = 'permission';
    public const ATTRIBUTE_AS_LABEL = 'attribute_as_label';
    public const ATTRIBUTE_AS_IMAGE = 'attribute_as_image';

    /** @var ReferenceEntityIdentifier */
    public $identifier;

    /** @var LabelCollection */
    public $labels;

    /** @var Image */
    public $image;

    /** @var int */
    public $recordCount;

    /** @var AttributeDetails[] */
    public $attributes;

    /** @var AttributeAsLabelReference */
    public $attributeAsLabel;

    /** @var AttributeAsImageReference */
    public $attributeAsImage;

    /** @var bool */
    public $isAllowedToEdit = true;

    private const EDIT_PERMISSION = 'edit';

    public function normalize(): array
    {
        return [
            self::IDENTIFIER   => (string) $this->identifier,
            self::LABELS       => $this->labels->normalize(),
            self::IMAGE        => $this->image->normalize(),
            self::RECORD_COUNT => $this->recordCount,
            self::ATTRIBUTES   => array_map(function (AttributeDetails $attribute) {
                return $attribute->normalize();
            }, $this->attributes),
            self::PERMISSION => [
                self::EDIT_PERMISSION => $this->isAllowedToEdit,
            ],
            self::ATTRIBUTE_AS_LABEL => ($this->attributeAsLabel->isEmpty()) ? null : $this->attributeAsLabel->normalize(),
            self::ATTRIBUTE_AS_IMAGE => ($this->attributeAsImage->isEmpty()) ? null : $this->attributeAsImage->normalize()
        ];
    }
}
