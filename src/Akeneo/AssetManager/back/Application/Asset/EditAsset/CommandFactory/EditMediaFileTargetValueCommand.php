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

class EditMediaFileTargetValueCommand extends AbstractEditValueCommand
{
    /** @var string */
    public $filePath;

    /** @var string */
    public $originalFilename;

    /** @var int */
    public $size;

    /** @var string */
    public $mimeType;

    /** @var string */
    public $extension;

    /** @var ?string */
    public $updatedAt;

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
}
