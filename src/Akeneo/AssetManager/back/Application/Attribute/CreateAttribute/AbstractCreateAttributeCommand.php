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
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @api
 */
abstract class AbstractCreateAttributeCommand
{
    public string $assetFamilyIdentifier;

    public string $code;

    public array $labels;

    public bool $isRequired;

    public bool $isReadOnly;

    public bool $valuePerChannel;

    public bool $valuePerLocale;

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $isReadOnly,
        bool $valuePerChannel,
        bool $valuePerLocale
    ) {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->labels = $labels;
        $this->isRequired = $isRequired;
        $this->isReadOnly = $isReadOnly;
        $this->valuePerChannel = $valuePerChannel;
        $this->valuePerLocale = $valuePerLocale;
    }
}
