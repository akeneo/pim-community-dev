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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\FindAssetFamilyPermissionsDetailsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamilyPermission\PermissionDetails;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAssetFamilyPermissionsDetails implements FindAssetFamilyPermissionsDetailsInterface
{
    /** @var array */
    public $results = [];

    public function save(array $permissions): void
    {
        Assert::allIsInstanceOf($permissions, PermissionDetails::class);
        $this->results = $permissions;
    }

    /**
     * @return PermissionDetails[]
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        return $this->results;
    }
}
