<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionHandler
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var SourceValueExtractor */
    private $sourceValueExtractor;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        SourceValueExtractor $sourceValueExtractor
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetRepository = $assetRepository;
        $this->sourceValueExtractor = $sourceValueExtractor;
    }

    public function __invoke(ExecuteNamingConventionCommand $command): void
    {
        $assetFamily = $this->getAssetFamily($command->assetFamilyIdentifier);
        $namingConvention = $assetFamily->getNamingConvention();
        if (!$namingConvention instanceof NamingConvention) {
            return;
        }

        $asset = $this->getAsset($command->assetFamilyIdentifier, $command->assetCode);
        $sourceValue = $this->sourceValueExtractor->extract($asset, $namingConvention);
        if (null === $sourceValue) {
            return;
        }

        // @todo AST-203: execute the split
        // @todo AST-203: save the result
    }

    private function getAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): AssetFamily
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        if (null === $assetFamily) {
            throw new AssetFamilyNotFoundException(
                sprintf("Asset family with code '%s' not found", $assetFamilyIdentifier->__toString())
            );
        }

        return $assetFamily;
    }

    private function getAsset(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $code): Asset
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $code);
        if (null === $asset) {
            throw new AssetNotFoundException();
        }

        return $asset;
    }
}
