<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkDataHydrator implements DataHydratorInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof MediaLinkAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        return MediaLinkData::createFromNormalize($normalizedData);
    }
}
