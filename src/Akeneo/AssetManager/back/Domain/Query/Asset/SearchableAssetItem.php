<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Asset;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchableAssetItem
{
    /** @var string */
    public $identifier;

    /** @var string */
    public $assetFamilyIdentifier;
    
    /** @var string */
    public $code;

    /** @var array */
    public $labels;
    
    /** @var array */
    public $values;

    /** @var \DateTimeImmutable */
    public $updatedAt;
}
