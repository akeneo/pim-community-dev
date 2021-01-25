<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\MassDeleteAssets;

use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;

/**
 * Handler to mass delete belonging to an asset family
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAssetsHandler
{
    private AssetRepositoryInterface $assetRepository;

    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    public function __invoke(MassDeleteAssetsCommand $massDeleteAssetsCommand): void
    {

    }
}
