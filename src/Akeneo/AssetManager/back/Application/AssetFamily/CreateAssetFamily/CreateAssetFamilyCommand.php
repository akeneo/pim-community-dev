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
    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var array */
    public $productLinkRules;

    public function __construct(string $code, array $labels, array $productLinkRules = [])
    {
        $this->code = $code;
        $this->labels = $labels;
        $this->productLinkRules = $productLinkRules;
    }
}
