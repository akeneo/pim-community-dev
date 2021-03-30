<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\AppendAttributeOption;

class AppendAttributeOptionCommand
{
    public string $assetFamilyIdentifier;
    public string $attributeCode;
    public string $optionCode;
    public ?array $labels;

    public function __construct(
        string $assetFamilyIdentifier,
        string $attributeCode,
        string $optionCode,
        ?array $labels
    ) {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->attributeCode = $attributeCode;
        $this->optionCode = $optionCode;
        $this->labels = $labels;
    }
}
