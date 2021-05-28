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

namespace Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAssetFamilyCommand
{
    public string $identifier;

    public array $labels;

    public array $productLinkRules;

    public array $transformations;

    public array $namingConvention;

    public function __construct(
        string $identifier,
        array $labels,
        array $productLinkRules = null,
        array $transformations = null,
        array $namingConvention = []
    ) {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->productLinkRules = $productLinkRules ?? [];
        $this->transformations = $transformations ?? [];
        $this->namingConvention = $namingConvention;
    }
}
