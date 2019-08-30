<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface GetAttributeTypeInterface
{
    public function fetch(AssetFamilyIdentifier $assetFamilyIdentifier, AttributeCode $attributeCode): string;
}
