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

namespace Akeneo\AssetManager\Application\Asset\EditAsset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\ValueUpdaterRegistryInterface;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAssetHandler
{
    private ValueUpdaterRegistryInterface $valueUpdaterRegistry;

    private AssetRepositoryInterface $assetRepository;

    private FileStorerInterface $storer;

    public function __construct(
        ValueUpdaterRegistryInterface $valueUpdaterRegistry,
        AssetRepositoryInterface $assetRepository,
        FileStorerInterface $storer
    ) {
        $this->valueUpdaterRegistry = $valueUpdaterRegistry;
        $this->assetRepository = $assetRepository;
        $this->storer = $storer;
    }

    /**
     * @param EditAssetCommand $editAssetCommand
     *
     * @throws FileRemovalException
     * @throws FileTransferException
     */
    public function __invoke(EditAssetCommand $editAssetCommand): void
    {
        $asset = $this->getAsset($editAssetCommand);
        $this->editValues($asset, $editAssetCommand);

        $this->assetRepository->update($asset);
    }

    private function getAsset(EditAssetCommand $editAssetCommand): Asset
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($editAssetCommand->assetFamilyIdentifier);
        $code = AssetCode::fromString($editAssetCommand->code);

        return $this->assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $code);
    }

    private function editValues(Asset $asset, EditAssetCommand $editAssetCommand): void
    {
        foreach ($editAssetCommand->editAssetValueCommands as $editAssetValueCommand) {
            $editValueUpdater = $this->valueUpdaterRegistry->getUpdater($editAssetValueCommand);
            ($editValueUpdater)($asset, $editAssetValueCommand);
        }
    }
}
