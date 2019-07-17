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
    /** @var string */
    public $identifier;

    /** @var array */
    public $labels;

    /** @var array|null */
    public $image;

    /** @var array */
    public $productLinkRules;

    public function __construct(string $identifier, array $labels, ?array $image, array $productLinkRules)
    {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->image = $image;
        $this->productLinkRules = $productLinkRules;
    }
}
