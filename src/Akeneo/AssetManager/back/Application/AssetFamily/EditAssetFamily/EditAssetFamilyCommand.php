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

namespace Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAssetFamilyCommand
{
    public string $identifier;

    public array $labels;

    public ?array $image = null;

    public ?string $attributeAsMainMedia = null;

    public ?array $productLinkRules = null;

    public ?array $transformations = null;

    public ?array $namingConvention = null;

    public function __construct(
        string $identifier,
        array $labels,
        ?array $image,
        ?string $attributeAsMainMedia,
        ?array $productLinkRules,
        ?array $transformations,
        ?array $namingConvention
    ) {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->image = $image;
        $this->attributeAsMainMedia = $attributeAsMainMedia;
        $this->productLinkRules = $productLinkRules;
        $this->transformations = $transformations;
        $this->namingConvention = $namingConvention;
    }
}
