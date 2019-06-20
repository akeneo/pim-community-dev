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

namespace Akeneo\AssetManager\Domain\Repository;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

interface AssetFamilyRepositoryInterface
{
    public function create(AssetFamily $assetFamily): void;

    public function update(AssetFamily $assetFamily): void;

    /**
     * @throws AssetFamilyNotFoundException
     */
    public function getByIdentifier(AssetFamilyIdentifier $identifier): AssetFamily;

    public function all(): \Iterator;

    /**
     * @throws AssetFamilyNotFoundException
     */
    public function deleteByIdentifier(AssetFamilyIdentifier $identifier): void;

    public function count(): int;
}
