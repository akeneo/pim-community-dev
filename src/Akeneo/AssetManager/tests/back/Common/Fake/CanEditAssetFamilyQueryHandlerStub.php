<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQuery;
use Akeneo\AssetManager\Application\AssetFamilyPermission\CanEditAssetFamily\CanEditAssetFamilyQueryHandler;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditAssetFamilyQueryHandlerStub extends CanEditAssetFamilyQueryHandler
{
    private bool $isAllowed = true;

    public function __invoke(CanEditAssetFamilyQuery $query): bool
    {
        Assert::stringNotEmpty($query->assetFamilyIdentifier);
        Assert::stringNotEmpty($query->securityIdentifier);

        return $this->isAllowed;
    }

    public function forbid(): void
    {
        $this->isAllowed = false;
    }
}
