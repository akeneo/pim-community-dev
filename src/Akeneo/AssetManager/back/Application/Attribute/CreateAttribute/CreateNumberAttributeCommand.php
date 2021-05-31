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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateNumberAttributeCommand extends AbstractCreateAttributeCommand
{
    public bool $decimalsAllowed;

    public ?string $minValue = null;

    public ?string $maxValue = null;

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $isReadOnly,
        bool $valuePerChannel,
        bool $valuePerLocale,
        bool $decimalsAllowed,
        ?string $minValue,
        ?string $maxValue
    ) {
        parent::__construct(
            $assetFamilyIdentifier,
            $code,
            $labels,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->decimalsAllowed = $decimalsAllowed;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }
}
