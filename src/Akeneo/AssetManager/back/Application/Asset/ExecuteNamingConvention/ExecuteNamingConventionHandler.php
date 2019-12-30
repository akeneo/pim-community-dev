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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
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

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetRepository = $assetRepository;
    }

    public function __invoke(ExecuteNamingConventionCommand $command): void
    {
        $assetFamily = $this->getAssetFamily($command);
        $namingConvention = $assetFamily->getNamingConvention();
        if (!$namingConvention instanceof NamingConvention) {
            return;
        }

        $asset = $this->getAsset($command);
        $sourceValue = $this->extractSourceValue($asset, $namingConvention);
        if (null === $sourceValue) {
            return;
        }

        // @todo: execute the split
        // @todo: save the result
    }

    /**
     * Put this code in a dedicated class?
     */
    private function extractSourceValue(Asset $asset, NamingConvention $namingConvention): ?string
    {
        $source = $namingConvention->getSource();
        if ($source->isAssetCode()) {
            return $asset->getCode()->__toString();
        }

        $valueKey = ValueKey::create(
            AttributeIdentifier::fromString($source->getProperty()),
            $source->getChannelReference(),
            $source->getLocaleReference()
        );
        $value = $asset->findValue($valueKey);
        if (null === $value) {
            return null;
        }

        $dataValue = $value->getData()->normalize();

        return is_string($dataValue) ? $dataValue : null;
    }

    private function getAssetFamily(ExecuteNamingConventionCommand $command): AssetFamily
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($command->assetFamilyIdentifier);
        if (null === $assetFamily) {
            throw new AssetFamilyNotFoundException(
                sprintf("Asset family with code '%s' not found", $command->assetFamilyIdentifier->__toString())
            );
        }

        return $assetFamily;
    }

    private function getAsset(ExecuteNamingConventionCommand $command): Asset
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode($command->assetFamilyIdentifier, $command->assetCode);
        if (null === $asset) {
            throw new AssetNotFoundException();
        }

        return $asset;
    }
}
