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
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAssetFamilyExists implements AssetFamilyExistsInterface
{
    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function __construct(InMemoryAssetFamilyRepository $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function withIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier, bool $caseSensitive = true): bool
    {
        return $this->assetFamilyRepository->hasAssetFamily($assetFamilyIdentifier, $caseSensitive);
    }
}
