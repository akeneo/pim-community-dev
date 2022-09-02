<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Exception;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;

final class CantDeleteMainMediaException extends \LogicException
{
    public static function withAttribute(AbstractAttribute $attribute): self
    {
        $message = sprintf(
            'Attribute "%s" cannot be deleted for the asset family "%s"  as it is used as attribute as main media.',
            $attribute->getIdentifier(),
            $attribute->getAssetFamilyIdentifier()
        );

        return new self($message);
    }
}
