<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Asset;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchableAssetItem
{
    public string $identifier;

    public string $assetFamilyIdentifier;
    
    public string $code;

    public array $labels;
    
    public array $values;

    public \DateTimeImmutable $updatedAt;
}
