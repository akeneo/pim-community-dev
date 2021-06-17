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
 */
class CreateMediaFileAttributeCommand extends AbstractCreateAttributeCommand
{
    public ?string $maxFileSize = null;

    public array $allowedExtensions;

    public string $mediaType;

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $isReadOnly,
        bool $valuePerChannel,
        bool $valuePerLocale,
        ?string $maxFileSize,
        array $allowedExtensions,
        string $mediaType
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

        $this->maxFileSize = $maxFileSize;
        $this->allowedExtensions = $allowedExtensions;
        $this->mediaType = $mediaType;
    }
}
