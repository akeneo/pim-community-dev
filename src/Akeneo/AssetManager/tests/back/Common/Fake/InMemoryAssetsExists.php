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
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAssetsExists
{
    private InMemoryAssetRepository $assetRepository;

    public function __construct(InMemoryAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function withAssetFamilyAndCodes(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $codes
    ): array {
        $result = [];
        foreach ($codes as $code) {
            $hasAsset = true;
            try {
                $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier,
                    AssetCode::fromString($code));
            } catch (AssetNotFoundException $exception) {
                $hasAsset = false;
            }

            if ($hasAsset) {
                $result[] = $code;
            }
        }

        return $result;
    }
}
