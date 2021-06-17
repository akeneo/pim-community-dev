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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateMediaFileAttributeCommandFactory extends AbstractCreateAttributeCommandFactory
{
    private const NO_LIMIT = null;

    public function supports(array $normalizedCommand): bool
    {
        return isset($normalizedCommand['type']) && MediaFileAttribute::ATTRIBUTE_TYPE === $normalizedCommand['type'];
    }

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand
    {
        $this->checkCommonProperties($normalizedCommand);

        $maxFileSize = isset($normalizedCommand['max_file_size']) ?
            (string)$normalizedCommand['max_file_size'] : self::NO_LIMIT;
        $allowedExtensions = isset($normalizedCommand['allowed_extensions']) ?
            $normalizedCommand['allowed_extensions'] : AttributeAllowedExtensions::ALL_ALLOWED;
        $mediaType = isset($normalizedCommand['media_type']) ?
            (string) $normalizedCommand['media_type'] : MediaType::IMAGE;

        return new CreateMediaFileAttributeCommand(
            $normalizedCommand['asset_family_identifier'],
            $normalizedCommand['code'],
            $normalizedCommand['labels'] ?? [],
            $normalizedCommand['is_required'] ?? false,
            $normalizedCommand['is_read_only'] ?? false,
            $normalizedCommand['value_per_channel'] ?? false,
            $normalizedCommand['value_per_locale'] ?? false,
            $maxFileSize,
            $allowedExtensions,
            $mediaType
        );
    }
}
