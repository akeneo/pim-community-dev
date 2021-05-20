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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * Read model representing an asset family for listing purpose (like in a grid)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetFamilyItem
{
    public const IDENTIFIER = 'identifier';

    public const LABELS = 'labels';

    public const IMAGE = 'image';

    public AssetFamilyIdentifier $identifier;

    public LabelCollection $labels;

    public Image $image;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => (string) $this->identifier,
            self::LABELS     => $this->labels->normalize(),
            self::IMAGE      => $this->image->normalize(),
        ];
    }
}
