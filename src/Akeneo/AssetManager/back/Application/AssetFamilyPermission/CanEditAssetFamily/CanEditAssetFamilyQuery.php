<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditAssetFamilyQuery
{
    /** @var string */
    public $assetFamilyIdentifier;

    /** @var string */
    public $securityIdentifier;

    public function __construct(string $assetFamilyIdentifier, string $securityIdentifier)
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->securityIdentifier = $securityIdentifier;
    }
}
