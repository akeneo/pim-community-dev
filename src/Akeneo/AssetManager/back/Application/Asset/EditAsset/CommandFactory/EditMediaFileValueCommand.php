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

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditMediaFileValueCommand extends AbstractEditValueCommand
{
    public string $filePath;
    public ?string $originalFilename;
    public ?int $size;
    public ?string $mimeType;
    public ?string $extension;
    public ?string $updatedAt;

    public function __construct(
        MediaFileAttribute $attribute,
        ?string $channel,
        ?string $locale,
        string $filePath,
        ?string $originalFilename,
        ?int $size,
        ?string $mimeType,
        ?string $extension,
        ?string $updatedAt
    ) {
        parent::__construct($attribute, $channel, $locale);

        $this->filePath = $filePath;
        $this->originalFilename = $originalFilename;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->extension = $extension;
        $this->updatedAt = $updatedAt;
    }

    public function normalize(): array
    {
        return [
            'attribute' => (string) $this->attribute->getIdentifier(),
            'channel' => $this->channel,
            'locale' => $this->locale,
            'data' => [
                'filePath' => $this->filePath,
                'originalFilename' => $this->originalFilename,
                'size' => $this->size,
                'mimeType' => $this->mimeType,
                'extension' => $this->extension,
                'updatedAt' => $this->updatedAt,
            ]
        ];
    }
}
