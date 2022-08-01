<?php

namespace Akeneo\AssetManager\Domain\Exception;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;

final class CantDeleteAttributeUsedAsLabelException extends \LogicException
{
    public static function withAttribute(AbstractAttribute $attribute): self
    {
        $message = sprintf(
            'Attribute "%s" cannot be deleted for the asset family "%s"  as it is used as attribute as label.',
            $attribute->getIdentifier(),
            $attribute->getAssetFamilyIdentifier()
        );

        return new self($message);
    }
}
