<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'image';

    /** @var AttributeMaxFileSize */
    private $maxFileSize;

    /** @var AttributeAllowedExtensions */
    private $extensions;

    protected function __construct(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeRequired $required,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxFileSize $maxFileSize,
        AttributeAllowedExtensions $extensions
    ) {
        parent::__construct($identifier, $enrichedEntityIdentifier, $code, $labelCollection, $order, $required,
            $valuePerChannel, $valuePerLocale);

        $this->maxFileSize = $maxFileSize;
        $this->extensions = $extensions;
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
        AttributeMaxFileSize $maxFileSize,
        AttributeAllowedExtensions $extensions
    ): self {
        return new self(
            $identifier,
            $enrichedEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $required,
            $valuePerChannel,
            $valuePerLocale,
            $maxFileSize,
            $extensions
        );
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'max_file_size' => $this->maxFileSize->floatValue(),
                'allowed_extensions' => $this->extensions->normalize()
            ]
        );
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
