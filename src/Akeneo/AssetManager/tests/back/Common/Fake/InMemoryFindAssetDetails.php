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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAssetDetails implements FindAssetDetailsInterface
{
    /** @var AssetDetails[] */
    private array $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function save(AssetDetails $assetDetails)
    {
        $normalized = $assetDetails->normalize();
        $assetFamilyIdentifier = $normalized['asset_family_identifier'];
        $code = $normalized['code'];

        $this->results[sprintf('%s____%s', $assetFamilyIdentifier, $code)] = $assetDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $assetCode
    ): ?AssetDetails {
        return $this->results[sprintf('%s____%s', $assetFamilyIdentifier, $assetCode)] ?? null;
    }
}
