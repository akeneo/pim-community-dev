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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateMediaLinkAttributeCommand extends AbstractCreateAttributeCommand
{
    public string $mediaType;

    public ?string $prefix = null;

    public ?string $suffix = null;

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $isReadOnly,
        bool $valuePerChannel,
        bool $valuePerLocale,
        string $mediaType,
        ?string $prefix,
        ?string $suffix
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

        $this->mediaType = $mediaType;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }
}
