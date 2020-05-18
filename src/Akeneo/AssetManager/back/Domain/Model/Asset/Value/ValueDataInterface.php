<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @api
 */
interface ValueDataInterface
{
    public function normalize();

    public static function createFromNormalize($normalizedData): ValueDataInterface;

    public function equals(ValueDataInterface $valueData): bool;
}
